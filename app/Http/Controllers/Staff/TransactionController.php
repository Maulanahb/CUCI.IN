<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTransactionRequest;
use App\Models\Customer;
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
        ]);

        $routePrefix = $request->routeIs('admin.*') ? 'admin' : 'staff';

        return view('staff.transactions.show', compact('transaction', 'routePrefix'));
    }

    /**
     * Mengubah status operasional cucian.
     */
    public function updateStatus(Request $request, Transaction $transaction): RedirectResponse
    {
        $validated = $request->validate([
            'status' => [
                'required',
                'in:Diterima,Diproses,Selesai,Diambil,Dibatalkan',
            ],
        ]);

        $transaction->update([
            'status' => $validated['status'],
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
            $transaction = CreateTransaction::execute(
                $customerId,
                $createdBy,
                $items
            );
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
