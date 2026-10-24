<?php

namespace App\Http\Controllers;

use App\Enums\TransactionStatus;
use App\Models\Customer;
use App\Models\LaundryStatusHistory;
use App\Models\Payment;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Tampilkan halaman dashboard untuk Administrator.
     */
    public function admin(): View
    {
        $today = today();

        $stats = [
            'total_users' => User::count(),
            'total_customers' => Customer::count(),
            'total_services' => Service::where('is_active', true)->count(),
            'total_transactions' => Transaction::count(),
        ];

        $statusCounts = $this->statusCounts();

        $revenueToday = (float) Payment::paid()
            ->whereDate('paid_at', $today)
            ->sum('amount');

        $revenueYesterday = (float) Payment::paid()
            ->whereDate('paid_at', $today->copy()->subDay())
            ->sum('amount');

        $revenueMonth = (float) Payment::paid()
            ->whereBetween('paid_at', [$today->copy()->startOfMonth(), $today->copy()->endOfDay()])
            ->sum('amount');

        $transactionsToday = Transaction::whereDate('created_at', $today)->count();

        [$paymentComposition, $receivables] = $this->paymentSummary();

        $finance = [
            'today' => $revenueToday,
            'month' => $revenueMonth,
            'receivables' => $receivables,
            'transactions_today' => $transactionsToday,
            'average_ticket' => $transactionsToday > 0 ? $revenueToday / $transactionsToday : 0.0,
            'trend' => $revenueYesterday > 0
                ? (($revenueToday - $revenueYesterday) / $revenueYesterday) * 100
                : null,
        ];

        $weeklyRevenue = $this->weeklyRevenue($today);
        $weeklyTotal = (float) $weeklyRevenue->sum('total');

        $paymentTotal = array_sum($paymentComposition);
        $paymentBreakdown = collect([
            'Lunas' => ['color' => '#10b981', 'dot' => 'bg-emerald-500'],
            'DP' => ['color' => '#f59e0b', 'dot' => 'bg-amber-500'],
            'Belum Bayar' => ['color' => '#f43f5e', 'dot' => 'bg-rose-500'],
        ])->map(fn (array $meta, string $status): array => [
            'status' => $status,
            'count' => $paymentComposition[$status],
            'percentage' => $paymentTotal > 0
                ? round(($paymentComposition[$status] / $paymentTotal) * 100, 1)
                : 0.0,
            ...$meta,
        ])->values();

        [$methodBreakdown, $methodTotal] = $this->paymentMethodBreakdown($today);

        $topServices = $this->topServices($today);

        $recentTransactions = Transaction::with(['customer', 'details.service'])
            ->latest('id')
            ->paginate(5, ['*'], 'transactions_page')
            ->withQueryString();

        $activeServices = Service::where('is_active', true)
            ->latest('id')
            ->take(4)
            ->get();

        $recentActivities = $this->recentActivities();
        $recentCustomers = $this->recentCustomers();

        return view('admin.dashboard', compact(
            'stats',
            'statusCounts',
            'finance',
            'weeklyRevenue',
            'weeklyTotal',
            'paymentBreakdown',
            'paymentTotal',
            'methodBreakdown',
            'methodTotal',
            'topServices',
            'recentTransactions',
            'activeServices',
            'recentActivities',
            'recentCustomers',
        ));
    }

    /**
     * Jumlah transaksi per status dalam satu kueri.
     *
     * @return array{diterima: int, diproses: int, selesai: int, diambil: int}
     */
    private function statusCounts(): array
    {
        $counts = Transaction::toBase()
            ->selectRaw(
                'count(case when status = ? then 1 end) as diterima, '
                .'count(case when status = ? then 1 end) as diproses, '
                .'count(case when status = ? then 1 end) as selesai, '
                .'count(case when status = ? then 1 end) as diambil',
                [
                    TransactionStatus::Diterima->value,
                    TransactionStatus::Diproses->value,
                    TransactionStatus::Selesai->value,
                    TransactionStatus::Diambil->value,
                ]
            )
            ->first();

        return [
            'diterima' => (int) ($counts->diterima ?? 0),
            'diproses' => (int) ($counts->diproses ?? 0),
            'selesai' => (int) ($counts->selesai ?? 0),
            'diambil' => (int) ($counts->diambil ?? 0),
        ];
    }

    /**
     * Pembaruan status terbaru beserta status sebelumnya untuk kebutuhan feed dashboard.
     *
     * @return Collection<int, LaundryStatusHistory>
     */
    private function recentActivities(int $limit = 5): Collection
    {
        $activities = LaundryStatusHistory::with([
            'changedBy:id,name',
            'transaction:id,transaction_code,customer_id',
            'transaction.customer:id,name',
            'transaction.statusHistories:id,transaction_id,status',
        ])
            ->latest('changed_at')
            ->latest('id')
            ->take($limit)
            ->get();

        $activities->each(function (LaundryStatusHistory $activity): void {
            $activity->previous_status = $activity->transaction?->statusHistories
                ->where('id', '<', $activity->id)
                ->sortByDesc('id')
                ->first()?->status;
        });

        return $activities;
    }

    /**
     * Pelanggan terbaru beserta jumlah transaksinya.
     *
     * @return Collection<int, Customer>
     */
    private function recentCustomers(int $limit = 5): Collection
    {
        return Customer::withCount('transactions')
            ->latest('created_at')
            ->latest('id')
            ->take($limit)
            ->get();
    }

    /**
     * Warna metode pembayaran yang konsisten di seluruh dashboard.
     */
    private function paymentMethodColor(string $method): string
    {
        return match ($method) {
            'Tunai' => 'bg-emerald-500',
            'Transfer' => 'bg-brand-500',
            default => 'bg-indigo-500',
        };
    }

    /**
     * Susun rincian nominal pembayaran per metode dari total yang sudah dikelompokkan.
     *
     * @param  Collection<string, float>  $totals
     * @return Collection<int, array{method: string, total: float, percentage: float, bar_class: string}>
     */
    private function methodBreakdown(Collection $totals): Collection
    {
        $grandTotal = (float) $totals->sum();

        return collect(['Tunai', 'Transfer', 'QRIS'])->map(fn (string $method): array => [
            'method' => $method,
            'total' => (float) ($totals[$method] ?? 0),
            'percentage' => $grandTotal > 0
                ? round(((float) ($totals[$method] ?? 0) / $grandTotal) * 100, 1)
                : 0.0,
            'bar_class' => $this->paymentMethodColor($method),
        ]);
    }

    /**
     * Hitung piutang dan komposisi status pembayaran seluruh transaksi non-batal.
     *
     * @return array{0: array<string, int>, 1: float}
     */
    private function paymentSummary(): array
    {
        $composition = ['Lunas' => 0, 'DP' => 0, 'Belum Bayar' => 0];
        $receivables = 0.0;

        Transaction::query()
            ->where('status', '!=', TransactionStatus::Dibatalkan->value)
            ->select(['id', 'total_amount'])
            ->withSum(['payments as paid_sum' => fn (Builder $query): Builder => $query->paid()], 'amount')
            ->chunkById(200, function (Collection $transactions) use (&$composition, &$receivables): void {
                foreach ($transactions as $transaction) {
                    $total = (float) $transaction->total_amount;
                    $paid = (float) ($transaction->paid_sum ?? 0);
                    $remaining = max(0.0, $total - $paid);
                    $receivables += $remaining;

                    if ($total > 0 && $remaining <= 0) {
                        $composition['Lunas']++;
                    } elseif ($paid > 0) {
                        $composition['DP']++;
                    } else {
                        $composition['Belum Bayar']++;
                    }
                }
            });

        return [$composition, $receivables];
    }

    /**
     * Susun pemasukan sah tujuh hari terakhir, termasuk hari tanpa transaksi.
     *
     * @return Collection<int, array{date: string, label: string, full_label: string, total: float, height: float}>
     */
    private function weeklyRevenue(Carbon $today): Collection
    {
        $dayNames = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];

        $revenueByDay = Payment::paid()
            ->whereBetween('paid_at', [$today->copy()->subDays(6)->startOfDay(), $today->copy()->endOfDay()])
            ->get(['amount', 'paid_at'])
            ->groupBy(fn (Payment $payment): string => $payment->paid_at->toDateString())
            ->map(fn (Collection $payments): float => (float) $payments->sum('amount'));

        $weeklyRevenue = collect(range(6, 0))->map(function (int $daysAgo) use ($today, $revenueByDay, $dayNames): array {
            $date = $today->copy()->subDays($daysAgo);

            return [
                'date' => $date->toDateString(),
                'label' => $dayNames[$date->dayOfWeek],
                'full_label' => $date->format('d/m'),
                'total' => (float) ($revenueByDay[$date->toDateString()] ?? 0),
            ];
        });

        $maxRevenue = (float) $weeklyRevenue->max('total');

        return $weeklyRevenue->map(function (array $day) use ($maxRevenue): array {
            $day['height'] = $maxRevenue > 0 && $day['total'] > 0
                ? max(2.0, ($day['total'] / $maxRevenue) * 100)
                : 0.0;

            return $day;
        });
    }

    /**
     * Komposisi nominal pembayaran sah per metode dalam 30 hari terakhir.
     *
     * @return array{0: Collection<int, array{method: string, total: float, percentage: float, bar_class: string}>, 1: float}
     */
    private function paymentMethodBreakdown(Carbon $today): array
    {
        $totals = Payment::paid()
            ->where('paid_at', '>=', $today->copy()->subDays(29)->startOfDay())
            ->get(['method', 'amount'])
            ->groupBy('method')
            ->map(fn (Collection $payments): float => (float) $payments->sum('amount'));

        return [$this->methodBreakdown($totals), (float) $totals->sum()];
    }

    /**
     * Lima layanan dengan kontribusi pendapatan tertinggi dalam 30 hari terakhir.
     *
     * @return Collection<int, object{service_name: string, quantity: float, revenue: float, share: float}>
     */
    private function topServices(Carbon $today): Collection
    {
        $topServices = TransactionDetail::query()
            ->join('transactions', 'transactions.id', '=', 'transaction_details.transaction_id')
            ->join('services', 'services.id', '=', 'transaction_details.service_id')
            ->where('transactions.status', '!=', TransactionStatus::Dibatalkan->value)
            ->where('transactions.created_at', '>=', $today->copy()->subDays(29)->startOfDay())
            ->groupBy('services.id', 'services.name')
            ->selectRaw(
                'services.name as service_name, '
                .'sum(transaction_details.weight) as quantity, '
                .'sum(transaction_details.subtotal) as revenue'
            )
            ->orderByDesc('revenue')
            ->limit(5)
            ->get();

        $maxRevenue = (float) ($topServices->max('revenue') ?? 0);

        return $topServices->map(function (object $service) use ($maxRevenue): object {
            $service->quantity = (float) $service->quantity;
            $service->revenue = (float) $service->revenue;
            $service->share = $maxRevenue > 0 ? ($service->revenue / $maxRevenue) * 100 : 0.0;

            return $service;
        });
    }

    /**
     * Tampilkan halaman dashboard untuk Staff.
     */
    public function staff(): View
    {
        $today = today();
        $statusCounts = $this->statusCounts();

        $overdueCounts = Transaction::toBase()
            ->whereIn('status', [
                TransactionStatus::Diterima->value,
                TransactionStatus::Diproses->value,
            ])
            ->whereNotNull('estimated_completed_at')
            ->where('estimated_completed_at', '<', now())
            ->selectRaw(
                'count(case when status = ? then 1 end) as diterima, '
                .'count(case when status = ? then 1 end) as diproses',
                [
                    TransactionStatus::Diterima->value,
                    TransactionStatus::Diproses->value,
                ]
            )
            ->first();

        $overdueByStatus = [
            'diterima' => (int) ($overdueCounts->diterima ?? 0),
            'diproses' => (int) ($overdueCounts->diproses ?? 0),
        ];

        $todayTransactions = Transaction::query()
            ->whereDate('created_at', $today)
            ->where('status', '!=', TransactionStatus::Dibatalkan->value)
            ->select(['id', 'total_amount'])
            ->withSum(['payments as paid_sum' => fn (Builder $query): Builder => $query->paid()], 'amount')
            ->get();

        $paidTodayCount = 0;
        $unpaidTodayCount = 0;

        foreach ($todayTransactions as $transaction) {
            $total = (float) $transaction->total_amount;
            $paid = (float) ($transaction->paid_sum ?? 0);

            if ($total > 0 && $paid >= $total) {
                $paidTodayCount++;
            } else {
                $unpaidTodayCount++;
            }
        }

        $cashByMethod = Payment::paid()
            ->whereDate('paid_at', $today)
            ->get(['method', 'amount'])
            ->groupBy('method')
            ->map(fn (Collection $payments): float => (float) $payments->sum('amount'));

        $cashBreakdown = $this->methodBreakdown($cashByMethod);
        $cashTotal = (float) $cashByMethod->sum();

        $queueTransactions = Transaction::query()
            ->with(['customer'])
            ->withSum(['payments as paid_sum' => fn (Builder $query): Builder => $query->paid()], 'amount')
            ->whereIn('status', [
                TransactionStatus::Diterima->value,
                TransactionStatus::Diproses->value,
                TransactionStatus::Selesai->value,
            ])
            ->orderByRaw(
                'case when status in (?, ?) and estimated_completed_at is not null and estimated_completed_at < ? then 0 else 1 end',
                [
                    TransactionStatus::Diterima->value,
                    TransactionStatus::Diproses->value,
                    now(),
                ]
            )
            ->orderByRaw('case when estimated_completed_at is null then 1 else 0 end')
            ->orderBy('estimated_completed_at')
            ->orderByDesc('id')
            ->paginate(6, ['*'], 'queue_page')
            ->withQueryString();

        $queueTransactions->getCollection()->transform(function (Transaction $transaction): Transaction {
            $status = $transaction->status instanceof TransactionStatus
                ? $transaction->status->value
                : (string) $transaction->status;

            $total = (float) $transaction->total_amount;
            $paid = (float) ($transaction->paid_sum ?? 0);

            $transaction->queue_payment_status = match (true) {
                $total > 0 && $paid >= $total => 'Lunas',
                $paid > 0 => 'DP',
                default => 'Belum Bayar',
            };

            $transaction->queue_is_overdue = $transaction->estimated_completed_at !== null
                && $transaction->estimated_completed_at->isPast()
                && in_array($status, [
                    TransactionStatus::Diterima->value,
                    TransactionStatus::Diproses->value,
                ], true);

            $transaction->queue_hours = $transaction->estimated_completed_at !== null
                ? (int) ceil(abs(now()->diffInHours($transaction->estimated_completed_at)))
                : null;

            return $transaction;
        });

        $stats = [
            'transactions_today' => $todayTransactions->count(),
            'in_progress' => $statusCounts['diterima'] + $statusCounts['diproses'],
            'overdue' => $overdueByStatus['diterima'] + $overdueByStatus['diproses'],
            'ready_for_pickup' => $statusCounts['selesai'],
        ];

        $activeServices = Service::where('is_active', true)
            ->latest('id')
            ->take(4)
            ->get();

        $recentActivities = $this->recentActivities();
        $recentCustomers = $this->recentCustomers();

        return view('staff.dashboard', compact(
            'stats',
            'statusCounts',
            'overdueByStatus',
            'queueTransactions',
            'cashBreakdown',
            'cashTotal',
            'paidTodayCount',
            'unpaidTodayCount',
            'activeServices',
            'recentActivities',
            'recentCustomers',
        ));
    }

    /**
     * Tampilkan halaman dashboard untuk Pelanggan (Customer).
     */
    public function customer(): View
    {
        /** @var User $user */
        $user = auth()->user();
        /** @var Customer|null $customer */
        $customer = $user->customer;

        if (! $customer) {
            return view('customer.dashboard', [
                'customer' => null,
                'activeTransactions' => collect(),
                'recentTransactions' => collect(),
                'stats' => [
                    'active_count' => 0,
                    'unpaid_amount' => 0.0,
                    'total_count' => 0,
                ],
            ]);
        }

        $allTransactions = $customer->transactions()
            ->with(['payments', 'details.service', 'statusHistories.changedBy'])
            ->latest('id')
            ->get();

        // 1. Cucian Aktif (Status Diterima, Diproses, Selesai)
        $activeTransactions = $allTransactions->filter(function (Transaction $trx): bool {
            $status = $trx->status instanceof TransactionStatus ? $trx->status->value : (string) $trx->status;

            return in_array($status, ['Diterima', 'Diproses', 'Selesai'], true);
        })->values();

        // 2. Transaksi Terakhir (5 transaksi terbaru)
        $recentTransactions = $allTransactions->take(5);

        // 3. Kalkulasi statistik ringkasan
        $unpaidAmount = 0.0;
        foreach ($allTransactions as $trx) {
            $status = $trx->status instanceof TransactionStatus ? $trx->status->value : (string) $trx->status;
            if ($status === 'Dibatalkan') {
                continue;
            }

            $paid = $trx->total_paid;
            $remaining = max(0.0, (float) $trx->total_amount - $paid);
            if ($remaining > 0) {
                $unpaidAmount += $remaining;
            }
        }

        $stats = [
            'active_count' => $activeTransactions->count(),
            'unpaid_amount' => $unpaidAmount,
            'total_count' => $allTransactions->count(),
        ];

        return view('customer.dashboard', [
            'customer' => $customer,
            'activeTransactions' => $activeTransactions,
            'recentTransactions' => $recentTransactions,
            'stats' => $stats,
        ]);
    }
}
