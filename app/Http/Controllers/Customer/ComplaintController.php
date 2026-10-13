<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreComplaintRequest;
use App\Models\Complaint;
use App\Models\Customer;
use App\Models\Transaction;
use App\Models\User;
use App\Services\ComplaintCodeGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class ComplaintController extends Controller
{
    /**
     * Tampilkan riwayat pengaduan milik pelanggan yang sedang login.
     */
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = auth()->user();
        /** @var Customer|null $customer */
        $customer = $user->customer;

        if (! $customer) {
            $stats = [
                'total_count' => 0,
                'pending_count' => 0,
                'in_progress_count' => 0,
                'resolved_count' => 0,
            ];

            return view('customer.complaints.index', [
                'complaints' => new LengthAwarePaginator([], 0, 8),
                'stats' => $stats,
            ]);
        }

        // Ambil query pengaduan milik customer
        $baseQuery = Complaint::query()
            ->whereHas('transaction', function ($q) use ($customer) {
                $q->where('customer_id', $customer->id);
            });

        // Ambil data untuk statistik keseluruhan customer
        $allCustomerComplaints = (clone $baseQuery)->get();
        $stats = [
            'total_count' => $allCustomerComplaints->count(),
            'pending_count' => $allCustomerComplaints->where('status', 'Menunggu')->count(),
            'in_progress_count' => $allCustomerComplaints->where('status', 'Diproses')->count(),
            'resolved_count' => $allCustomerComplaints->where('status', 'Selesai')->count(),
        ];

        // Filter status
        $query = $baseQuery
            ->with(['transaction.details.service', 'handler'])
            ->latest('id');

        if ($status = $request->query('status')) {
            if (in_array($status, ['Menunggu', 'Diproses', 'Selesai'], true)) {
                $query->where('status', $status);
            }
        }

        // Filter pencarian
        if ($search = trim((string) $request->query('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('complaint_code', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhereHas('transaction', function ($tq) use ($search) {
                        $tq->where('transaction_code', 'like', "%{$search}%");
                    });
            });
        }

        $complaints = $query->paginate(8)->withQueryString();

        return view('customer.complaints.index', [
            'complaints' => $complaints,
            'stats' => $stats,
        ]);
    }

    /**
     * Tampilkan formulir pengajuan pengaduan baru.
     */
    public function create(Request $request): View
    {
        /** @var User $user */
        $user = auth()->user();
        /** @var Customer|null $customer */
        $customer = $user->customer;

        $eligibleTransactions = collect();
        $selectedTransaction = null;
        $preselectedCode = trim((string) $request->query('transaction_code'));

        if ($customer) {
            // Ambil transaksi milik customer yang bukan dibatalkan
            $allTransactions = $customer->transactions()
                ->with(['details.service', 'complaints'])
                ->where('status', '!=', 'Dibatalkan')
                ->latest('id')
                ->get();

            // Saring transaksi yang belum punya pengaduan aktif ('Menunggu' / 'Diproses')
            $eligibleTransactions = $allTransactions->filter(function (Transaction $trx): bool {
                return ! $trx->complaints->contains(function (Complaint $complaint): bool {
                    return in_array($complaint->status, ['Menunggu', 'Diproses'], true);
                });
            });

            if ($preselectedCode !== '') {
                $selectedTransaction = $allTransactions->firstWhere('transaction_code', $preselectedCode);
            }
        }

        return view('customer.complaints.create', [
            'eligibleTransactions' => $eligibleTransactions,
            'selectedTransaction' => $selectedTransaction,
            'preselectedCode' => $preselectedCode,
        ]);
    }

    /**
     * Simpan pengaduan baru yang diajukan pelanggan.
     */
    public function store(StoreComplaintRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $complaint = Complaint::create([
            'complaint_code' => ComplaintCodeGenerator::generate(),
            'transaction_id' => $validated['transaction_id'],
            'subject' => $validated['subject'],
            'description' => $validated['description'],
            'status' => 'Menunggu',
            'deadline_at' => now()->addDays(2),
        ]);

        return redirect()
            ->route('customer.complaints.show', $complaint)
            ->with('status', 'Pengaduan dengan nomor '.$complaint->complaint_code.' berhasil dikirim. Staf kami akan segera menindaklanjuti.');
    }

    /**
     * Tampilkan rincian pengaduan beserta status penanganan & tanggapan petugas.
     */
    public function show(Complaint $complaint): View
    {
        /** @var User $user */
        $user = auth()->user();
        /** @var Customer|null $customer */
        $customer = $user->customer;

        // Otorisasi: pengaduan harus terkait dengan transaksi milik customer yang login
        if (! $customer || $complaint->transaction?->customer_id !== $customer->id) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat pengaduan ini.');
        }

        $complaint->load([
            'transaction.details.service',
            'transaction.payments',
            'handler',
        ]);

        return view('customer.complaints.show', [
            'complaint' => $complaint,
        ]);
    }
}
