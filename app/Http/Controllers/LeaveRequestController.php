<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LeaveRequestController extends Controller
{
    /**
     * User (Peserta Magang) - Tampilkan halaman daftar & form pengajuan izin/sakit/cuti
     */
    public function userIndex()
    {
        $user = Auth::user();
        $leaveRequests = LeaveRequest::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('presensi.leave_index', compact('leaveRequests'));
    }

    /**
     * User (Peserta Magang) - Submit Form Pengajuan Izin / Sakit / Cuti
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => ['required', 'in:sakit,izin,cuti'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'max:1000'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'], // Max 5MB
        ]);

        $user = Auth::user();

        // Cek overlap tanggal pengajuan yang sudah disetujui/pending
        $overlapping = LeaveRequest::where('user_id', $user->id)
            ->where('status', '!=', 'rejected')
            ->where(function ($query) use ($request) {
                $query->whereBetween('start_date', [$request->start_date, $request->end_date])
                    ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
                    ->orWhere(function ($q) use ($request) {
                        $q->where('start_date', '<=', $request->start_date)
                          ->where('end_date', '>=', $request->end_date);
                    });
            })
            ->first();

        if ($overlapping) {
            return back()->with('error', 'Anda sudah memiliki pengajuan izin/sakit/cuti pada rentang tanggal tersebut.');
        }

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('leave_attachments', 'public');
        }

        LeaveRequest::create([
            'user_id' => $user->id,
            'type' => $request->type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'reason' => $request->reason,
            'attachment' => $attachmentPath,
            'status' => 'pending',
        ]);

        return redirect()->route('presensi.leave.index')->with('success', 'Pengajuan ' . strtoupper($request->type) . ' berhasil dikirim dan menunggu persetujuan Admin.');
    }

    /**
     * Admin - Tampilkan Daftar Pengajuan Izin / Sakit / Cuti Peserta Magang
     */
    public function adminIndex(Request $request)
    {
        $status = $request->input('status', 'all');
        $search = $request->input('search');

        $query = LeaveRequest::with(['user', 'approver']);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('asal_instansi_kampus', 'like', "%{$search}%");
            });
        }

        $leaveRequests = $query->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        $pendingCount = LeaveRequest::where('status', 'pending')->count();
        $approvedCount = LeaveRequest::where('status', 'approved')->count();
        $rejectedCount = LeaveRequest::where('status', 'rejected')->count();

        return view('admin.leave_requests', compact(
            'leaveRequests',
            'status',
            'search',
            'pendingCount',
            'approvedCount',
            'rejectedCount'
        ));
    }

    /**
     * Admin - Setujui Pengajuan Izin/Sakit/Cuti
     */
    public function approve(Request $request, $id)
    {
        $leaveRequest = LeaveRequest::findOrFail($id);

        if ($leaveRequest->status === 'approved') {
            return back()->with('info', 'Pengajuan ini sudah disetujui sebelumnya.');
        }

        $leaveRequest->update([
            'status' => 'approved',
            'admin_note' => $request->input('admin_note'),
            'approved_by' => Auth::id(),
        ]);

        // Buat/Update status presensi pada attendances table untuk setiap hari pada periode pengajuan
        $period = CarbonPeriod::create($leaveRequest->start_date, $leaveRequest->end_date);

        foreach ($period as $date) {
            $dateStr = $date->toDateString();
            
            Attendance::updateOrCreate(
                [
                    'user_id' => $leaveRequest->user_id,
                    'date' => $dateStr,
                ],
                [
                    'status' => $leaveRequest->type, // sakit, izin, atau cuti
                ]
            );
        }

        return back()->with('success', 'Pengajuan ' . strtoupper($leaveRequest->type) . ' milik ' . $leaveRequest->user->name . ' telah disetujui.');
    }

    /**
     * Admin - Tolak Pengajuan Izin/Sakit/Cuti
     */
    public function reject(Request $request, $id)
    {
        $leaveRequest = LeaveRequest::findOrFail($id);

        $request->validate([
            'admin_note' => ['required', 'string', 'max:500'],
        ], [
            'admin_note.required' => 'Alasan penolakan wajib diisi untuk pemberitahuan ke peserta magang.'
        ]);

        $previousStatus = $leaveRequest->status;

        $leaveRequest->update([
            'status' => 'rejected',
            'admin_note' => $request->admin_note,
            'approved_by' => Auth::id(),
        ]);

        // Jika sebelumnya sempat disetujui, kembalikan record attendance yang belum di-checkin
        if ($previousStatus === 'approved') {
            $period = CarbonPeriod::create($leaveRequest->start_date, $leaveRequest->end_date);
            foreach ($period as $date) {
                $dateStr = $date->toDateString();
                $att = Attendance::where('user_id', $leaveRequest->user_id)
                    ->whereDate('date', $dateStr)
                    ->whereIn('status', ['sakit', 'izin', 'cuti'])
                    ->first();

                if ($att && !$att->check_in_time) {
                    $att->delete();
                }
            }
        }

        return back()->with('success', 'Pengajuan ' . strtoupper($leaveRequest->type) . ' milik ' . $leaveRequest->user->name . ' telah ditolak.');
    }
}
