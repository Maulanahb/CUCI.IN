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
     * Tampilkan halaman rincian transaksi.
     */
    public function show(Request $request, Transaction $transaction): View
    {
        $transaction->load(['details.service', 'customer']);
        $routePrefix = $request->routeIs('admin.*') ? 'admin' : 'staff';

        return view('staff.transactions.show', compact('transaction', 'routePrefix'));
    }

    /**
     * Simpan transaksi baru yang diinput oleh Staff.
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
            $transaction = CreateTransaction::execute($customerId, $createdBy, $items);
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
