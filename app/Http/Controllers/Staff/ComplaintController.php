<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\UpdateComplaintRequest;
use App\Models\Complaint;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ComplaintController extends Controller
{
    /**
     * Menampilkan daftar tiket pengaduan pelanggan untuk ditangani staf.
     */
    public function index(Request $request): View
    {
        $baseQuery = Complaint::query();

        // Hitung statistik untuk badge ringkasan
        $stats = [
            'total' => (clone $baseQuery)->count(),
            'pending' => (clone $baseQuery)->where('status', 'Menunggu')->count(),
            'in_progress' => (clone $baseQuery)->where('status', 'Diproses')->count(),
            'resolved' => (clone $baseQuery)->where('status', 'Selesai')->count(),
        ];

        $query = Complaint::with(['transaction.customer', 'handler'])
            ->latest('id');

        // Filter status tiket
        if ($status = $request->query('status')) {
            if (in_array($status, ['Menunggu', 'Diproses', 'Selesai'], true)) {
                $query->where('status', $status);
            }
        }

        // Filter pencarian (kode tiket, subjek, nomor nota, nama pelanggan)
        if ($search = trim((string) $request->query('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('complaint_code', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhereHas('transaction', function ($tq) use ($search) {
                        $tq->where('transaction_code', 'like', "%{$search}%")
                            ->orWhereHas('customer', function ($cq) use ($search) {
                                $cq->where('name', 'like', "%{$search}%")
                                    ->orWhere('phone', 'like', "%{$search}%");
                            });
                    });
            });
        }

        $complaints = $query->paginate(10)->withQueryString();

        return view('staff.complaints.index', [
            'complaints' => $complaints,
            'stats' => $stats,
            'currentStatus' => $status,
            'currentSearch' => $search,
        ]);
    }

    /**
     * Menampilkan detail tiket pengaduan, bukti kendala, serta form tindak lanjut staf.
     */
    public function show(Complaint $complaint): View
    {
        $complaint->load([
            'transaction.customer',
            'transaction.details.service',
            'handler',
        ]);

        return view('staff.complaints.show', [
            'complaint' => $complaint,
        ]);
    }

    /**
     * Memperbarui status penanganan dan tanggapan staf terhadap tiket komplain.
     */
    public function update(UpdateComplaintRequest $request, Complaint $complaint): RedirectResponse
    {
        $validated = $request->validated();

        $complaint->status = $validated['status'];

        if (array_key_exists('response', $validated)) {
            $complaint->response = $validated['response'];
        }

        // Catat staf yang menangani serta waktu penanganan
        $complaint->handled_by = auth()->id();
        $complaint->handled_at = now();
        $complaint->save();

        return redirect()
            ->route('staff.complaints.show', $complaint)
            ->with('status', 'Tiket pengaduan '.$complaint->complaint_code.' berhasil diperbarui.');
    }
}
