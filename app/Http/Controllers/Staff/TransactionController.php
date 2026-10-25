<?php

namespace App\Http\Controllers\Staff;

use App\Enums\TransactionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTransactionRequest;
use App\Models\Customer;
use App\Models\LaundryStatusHistory;
use App\Models\Service;
use App\Models\Transaction;
use App\Services\CreateTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use InvalidArgumentException;

class TransactionController extends Controller
{
    /**
     * Menampilkan daftar transaksi.
     */
    public function index(Request $request): View
    {
        $query = Transaction::with([
            'customer',
            'details.service',
            'payments',
        ])->latest();

        // Pencarian berdasarkan kode transaksi atau nama customer
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('transaction_code', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($customerQuery) use ($search) {
                        $customerQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Filter berdasarkan status cucian
        if ($request->filled('status')) {
            if ($request->status === 'unclaimed') {
                $query->unclaimedStorage(14);
            } else {
                $query->where('status', $request->status);
            }
        }

        $unclaimedCount = Transaction::unclaimedStorage(14)->count();

        $transactions = $query->paginate(10)->withQueryString();

        return view('staff.transactions.index', compact('transactions', 'unclaimedCount'));
    }

    /**
     * Tampilkan form pembuatan transaksi baru (POS).
     */
    public function create(Request $request): View
    {
        $customers = Customer::query()->orderBy('name')->get();
        $services = Service::query()->where('is_active', true)->orderBy('name')->get();
        $routePrefix = $request->routeIs('admin.*') ? 'admin' : 'staff';

        return view('staff.transactions.create', compact('customers', 'services', 'routePrefix'));
    }

    /**
     * Menampilkan rincian/detail transaksi.
     */
    public function show(Request $request, Transaction $transaction): View
    {
        $transaction->load([
            'customer',
            'details.service',
            'statusHistories.changedBy',
            'payments.creator',
        ]);

        $routePrefix = $request->routeIs('admin.*') ? 'admin' : 'staff';

        return view('staff.transactions.show', compact('transaction', 'routePrefix'));
    }

    /**
     * Tampilkan halaman cetak nota struk kasir thermal (print-friendly).
     */
    public function print(Request $request, Transaction $transaction): View
    {
        $transaction->load([
            'customer',
            'details.service',
            'creator',
            'payments',
        ]);

        $routePrefix = $request->routeIs('admin.*') ? 'admin' : 'staff';

        return view('staff.transactions.print', compact('transaction', 'routePrefix'));
    }

    /**
     * Mengubah status operasional cucian.
     *
     * @throws ValidationException
     */
    public function updateStatus(Request $request, Transaction $transaction): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'status' => [
                'required',
                'in:Diterima,Diproses,Selesai,Diambil,Dibatalkan',
            ],
            'notes' => [
                'nullable',
                'string',
                'max:500',
            ],
        ]);

        $newStatus = TransactionStatus::from($validated['status']);
        $notes = ! empty($validated['notes']) ? trim($validated['notes']) : null;

        DB::transaction(function () use ($transaction, $newStatus, $notes): void {
            // Kunci baris agar dua kasir tidak mengubah status bersamaan.
            $current = Transaction::query()
                ->whereKey($transaction->id)
                ->lockForUpdate()
                ->firstOrFail();

            $currentStatus = $current->status instanceof TransactionStatus
                ? $current->status
                : TransactionStatus::from((string) $current->status);

            // 1. Cek apakah status saat ini adalah terminal (Diambil atau Dibatalkan)
            if ($currentStatus->isTerminal()) {
                throw ValidationException::withMessages([
                    'status' => "Transaksi telah berstatus {$currentStatus->value} dan tidak dapat diubah lagi.",
                ]);
            }

            // 2. Cek apakah status baru sama dengan status saat ini
            if ($newStatus === $currentStatus) {
                if (! empty($notes)) {
                    LaundryStatusHistory::create([
                        'transaction_id' => $current->id,
                        'status' => $currentStatus,
                        'notes' => $notes,
                        'changed_by' => auth()->id(),
                        'changed_at' => now(),
                    ]);

                    return;
                }

                throw ValidationException::withMessages([
                    'status' => 'Status yang dipilih sama dengan status saat ini.',
                ]);
            }

            // 3. Cek jika status baru adalah Dibatalkan, wajib ada alasan pembatalan
            if ($newStatus === TransactionStatus::Dibatalkan && empty($notes)) {
                throw ValidationException::withMessages([
                    'notes' => 'Alasan pembatalan wajib diisi.',
                ]);
            }

            // 4. Cek jika status baru merupakan rollback (mundur), wajib ada catatan alasan
            if ($currentStatus->isRollbackTo($newStatus) && empty($notes)) {
                throw ValidationException::withMessages([
                    'notes' => 'Alasan koreksi status wajib diisi saat memundurkan status cucian.',
                ]);
            }

            // 5. Cek jika status baru adalah Diambil, wajib lunas
            if ($newStatus === TransactionStatus::Diambil && ! $current->isPaidOff()) {
                throw ValidationException::withMessages([
                    'status' => 'Cucian belum dapat diserahkan karena pembayaran belum lunas. Sisa tagihan: Rp '.number_format($current->remaining_amount, 0, ',', '.'),
                ]);
            }

            $current->update([
                'status' => $newStatus,
            ]);

            LaundryStatusHistory::create([
                'transaction_id' => $current->id,
                'status' => $newStatus,
                'notes' => $notes,
                'changed_by' => auth()->id(),
                'changed_at' => now(),
            ]);
        });

        if ($request->expectsJson()) {
            $transaction->refresh();
            $latestNotes = $transaction->statusHistories()->latest('id')->value('notes') ?? '';

            return response()->json([
                'success' => true,
                'message' => 'Status transaksi berhasil diperbarui.',
                'transaction' => [
                    'id' => $transaction->id,
                    'status' => $transaction->status instanceof TransactionStatus ? $transaction->status->value : (string) $transaction->status,
                    'notes' => $latestNotes,
                ],
            ]);
        }

        return redirect()
            ->back()
            ->with('success', 'Status transaksi berhasil diperbarui.');
    }

    /**
     * Simpan transaksi baru yang diinput oleh Staff atau Admin.
     *
     * @throws ValidationException
     */
    public function store(StoreTransactionRequest $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validated();

        $customerId = (int) $validated['customer_id'];
        $items = $validated['items'];
        $createdBy = (int) auth()->id();

        try {
            $transaction = CreateTransaction::execute([
                'customer_id' => $customerId,
                'created_by' => $createdBy,
                'items' => $items,
                'pay_now' => $request->boolean('pay_now'),
                'payment_method' => $validated['payment_method'] ?? 'Tunai',
                'payment_amount' => $validated['payment_amount'] ?? null,
                'payment_choice' => $validated['payment_choice'] ?? null,
            ]);
        } catch (InvalidArgumentException $e) {
            $errorMessage = $e->getMessage();
            $errorKey = str_contains(strtolower($errorMessage), 'dp') || str_contains(strtolower($errorMessage), 'pembayaran')
                ? 'payment_amount'
                : 'items';

            throw ValidationException::withMessages([
                $errorKey => [$errorMessage],
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'id' => $transaction->id,
                'transaction_code' => $transaction->transaction_code,
            ], 201);
        }

        $routePrefix = $request->routeIs('admin.*') ? 'admin' : 'staff';

        return redirect()->route("{$routePrefix}.transactions.show", $transaction)
            ->with('success', "Transaksi berhasil disimpan dengan kode nota {$transaction->transaction_code}.");
    }
}
