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
            $query->where('status', $request->status);
        }

        $transactions = $query->paginate(10)->withQueryString();

        return view('staff.transactions.index', compact('transactions'));
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
    public function updateStatus(Request $request, Transaction $transaction): RedirectResponse
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

        $currentStatus = $transaction->status instanceof TransactionStatus
            ? $transaction->status
            : TransactionStatus::from((string) $transaction->status);

        $newStatus = TransactionStatus::from($validated['status']);
        $notes = ! empty($validated['notes']) ? trim($validated['notes']) : null;

        // 1. Cek apakah status saat ini adalah terminal (Diambil atau Dibatalkan)
        if ($currentStatus->isTerminal()) {
            throw ValidationException::withMessages([
                'status' => "Transaksi telah berstatus {$currentStatus->value} dan tidak dapat diubah lagi.",
            ]);
        }

        // 2. Cek apakah status baru sama dengan status saat ini
        if ($newStatus === $currentStatus) {
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
        if ($newStatus === TransactionStatus::Diambil && ! $transaction->isPaidOff()) {
            throw ValidationException::withMessages([
                'status' => 'Cucian belum dapat diserahkan karena pembayaran belum lunas. Sisa tagihan: Rp '.number_format($transaction->remaining_amount, 0, ',', '.'),
            ]);
        }

        $transaction->update([
            'status' => $newStatus,
        ]);

        LaundryStatusHistory::create([
            'transaction_id' => $transaction->id,
            'status' => $newStatus,
            'notes' => $notes,
            'changed_by' => auth()->id(),
            'changed_at' => now(),
        ]);

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
            ]);
        } catch (InvalidArgumentException $e) {
            throw ValidationException::withMessages([
                'items' => [$e->getMessage()],
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
