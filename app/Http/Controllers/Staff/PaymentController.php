<?php

namespace App\Http\Controllers\Staff;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentRequest;use App\Http\Requests\VerifyPaymentRequest;
use App\Models\Payment;
use App\Models\Transaction;
use App\Services\RecordPayment;
use App\Services\VerifyPayment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentController extends Controller
{
    /**
     * Pilihan tab filter status pada panel verifikasi.
     *
     * @var list<string>
     */
    private const VERIFICATION_FILTERS = ['Menunggu Verifikasi', 'Terverifikasi', 'Ditolak', 'Semua'];

    /**
     * Panel verifikasi kasir: daftar pembayaran Transfer/QRIS beserta status verifikasinya.
     */
    public function index(Request $request): View
    {
        $statusFilter = (string) $request->query('status', Payment::PENDING_STATUS);
        $statusFilter = in_array($statusFilter, self::VERIFICATION_FILTERS, true) ? $statusFilter : Payment::PENDING_STATUS;
        $search = trim((string) $request->query('search'));
        $routePrefix = $request->routeIs('admin.*') ? 'admin' : 'staff';

        $nonCashPayments = Payment::query()->whereIn('method', Payment::PROOF_METHODS);

        /** @var array<string, int> $counts */
        $counts = (clone $nonCashPayments)
            ->selectRaw('verification_status, COUNT(*) as aggregate')
            ->groupBy('verification_status')
            ->pluck('aggregate', 'verification_status')
            ->map(fn ($count): int => (int) $count)
            ->all();

        $query = $nonCashPayments
            ->with(['transaction.customer', 'creator', 'verifier'])
            ->when($statusFilter !== 'Semua', fn ($q) => $q->where('verification_status', $statusFilter));

        if ($search !== '') {
            $query->whereHas('transaction', function ($transactionQuery) use ($search) {
                $transactionQuery
                    ->where('transaction_code', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($customerQuery) use ($search) {
                        $customerQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Antrean menunggu diurutkan dari yang paling lama agar diproses lebih dulu.
        $statusFilter === Payment::PENDING_STATUS
            ? $query->oldest('paid_at')->oldest('id')
            : $query->latest('id');

        $payments = $query->paginate(10)->withQueryString();

        return view('staff.payments.index', compact('payments', 'counts', 'statusFilter', 'search', 'routePrefix'));
    }

    /**
     * Menampilkan detail pembayaran beserta pratinjau bukti untuk diverifikasi.
     */
    public function show(Request $request, Payment $payment): View
    {
        $payment->load([
            'transaction.customer',
            'transaction.payments.creator',
            'creator',
            'verifier',
        ]);

        $routePrefix = $request->routeIs('admin.*') ? 'admin' : 'staff';

        return view('staff.payments.show', compact('payment', 'routePrefix'));
    }

    /**
     * Tampilkan berkas bukti pembayaran dari disk privat.
     */
    public function proof(Payment $payment): StreamedResponse
    {
        if (! $payment->proof_file || ! Storage::disk(Payment::PROOF_DISK)->exists($payment->proof_file)) {
            abort(404, 'Berkas bukti pembayaran tidak ditemukan.');
        }

        $disk = Storage::disk(Payment::PROOF_DISK);
        $stream = $disk->readStream($payment->proof_file);

        if (! is_resource($stream)) {
            abort(404, 'Berkas bukti pembayaran tidak ditemukan.');
        }

        return response()->stream(function () use ($stream): void {
            fpassthru($stream);
            fclose($stream);
        }, 200, [
            'Content-Type' => $disk->mimeType($payment->proof_file) ?: 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="'.basename($payment->proof_file).'"',
        ]);
    }

    /**
     * Mencatat pembayaran baru dan mengunggah bukti jika diperlukan.
     */
    public function store(
        StorePaymentRequest $request,
        Transaction $transaction
    ): JsonResponse|RedirectResponse {
        $validated = $request->validated();

        try {
            $payment = RecordPayment::execute(
                transaction: $transaction,
                amount: (float) $validated['amount'],
                method: (string) $validated['method'],
                userId: (int) auth()->id(),
                cashReceived: isset($validated['cash_received'])
                    ? (float) $validated['cash_received']
                    : null
            );

            // Bukti Transfer/QRIS disimpan dan menunggu verifikasi.
            if (
                $request->hasFile('proof_file')
                && in_array($validated['method'], ['Transfer', 'QRIS'], true)
            ) {
                $path = $request->file('proof_file')->store(
                    Payment::PROOF_DIRECTORY,
                    Payment::PROOF_DISK
                );

                $payment->update([
                    'proof_file' => $path,
                    'verification_status' => 'Menunggu Verifikasi',
                ]);
            }
        } catch (InvalidArgumentException $e) {
            throw ValidationException::withMessages([
                'amount' => [$e->getMessage()],
            ]);
        }

        $formattedAmount = number_format(
            (float) $payment->amount,
            0,
            ',',
            '.'
        );

        $successMessage = "Pembayaran sebesar Rp {$formattedAmount} ({$payment->method}) berhasil dicatat.";

        // Jika dipanggil melalui AJAX/API, kembalikan respons JSON.
        if ($request->expectsJson()) {
            $transaction->refresh();
            $transaction->load('payments');

            $paymentStatusValue = (string) $transaction->payment_status;

            return response()->json([
                'success' => true,
                'message' => $successMessage,
                'transaction' => [
                    'id' => $transaction->id,
                    'paid_amount' => (float) $transaction->total_paid,
                    'paid_formatted' => 'Rp '.number_format(
                        (float) $transaction->total_paid,
                        0,
                        ',',
                        '.'
                    ),
                    'remaining_amount' => (float) $transaction->remaining_amount,
                    'remaining_formatted' => 'Rp '.number_format(
                        (float) $transaction->remaining_amount,
                        0,
                        ',',
                        '.'
                    ),
                    'payment_status' => $paymentStatusValue,
                    'payments' => $transaction->payments->map(fn ($p) => [
                        'id' => $p->id,
                        'amount_formatted' => 'Rp '.number_format(
                            (float) $p->amount,
                            0,
                            ',',
                            '.'
                        ),
                        'method' => $p->method ?? 'Tunai',
                        'created_at' => $p->paid_at?->format('d M Y, H:i')
                            ?? $p->created_at?->format('d M Y, H:i'),
                        'status' => $p->verification_status ?? 'Terverifikasi',
                    ])->values()->all(),
                ],
            ]);
        }

        return redirect()->back()->with('success', $successMessage);
    }

    /**
     * Memverifikasi (approve) atau menolak (reject) bukti pembayaran non-tunai.
     *
     * @throws ValidationException
     */
    public function verify(VerifyPaymentRequest $request, Payment $payment): RedirectResponse
    {
        $validated = $request->validated();

        try {
            $payment = VerifyPayment::execute(
                payment: $payment,
                decision: $validated['verification_status'],
                verifierId: (int) $request->user()->id,
                notes: $validated['verification_notes'] ?? null,
            );
        } catch (InvalidArgumentException $e) {
            throw ValidationException::withMessages([
                'verification_status' => [$e->getMessage()],
            ]);
        }

        $formattedAmount = number_format((float) $payment->amount, 0, ',', '.');
        $message = $payment->verification_status === VerifyPayment::APPROVE
            ? "Pembayaran Rp {$formattedAmount} berhasil diverifikasi."
            : "Pembayaran Rp {$formattedAmount} ditolak.";

        return redirect()
            ->back()
            ->with('success', $message);
    }
}
