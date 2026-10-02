<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\OfficeLocation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    /**
     * Dashboard Rekapitulasi Presensi & Analitik
     */
    public function dashboard(Request $request)
    {
        $today = Carbon::now('Asia/Jakarta')->toDateString();

        // Presensi Terbaru Hari Ini (Maksimal 5 Data Terkini)
        $recentAttendances = Attendance::with('user')
            ->whereDate('date', $today)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Stats Ringkasan Hari Ini
        $totalMagang = User::where('role', 'magang')->where('is_active', true)->count();
        $todayAttending = Attendance::where('date', $today)->whereIn('status', ['tepat_waktu', 'terlambat'])->count();
        $todayLate = Attendance::where('date', $today)->where('status', 'terlambat')->count();
        $todayLeave = Attendance::where('date', $today)->whereIn('status', ['sakit', 'izin', 'cuti'])->count();
        $todayNotYet = max(0, $totalMagang - $todayAttending - $todayLeave);

        $pendingLeaveCount = LeaveRequest::where('status', 'pending')->count();
        $suspiciousGpsCount = Attendance::where('date', $today)->where(function($q) {
            $q->where('is_mock_location', true)->orWhere('is_suspicious', true);
        })->count();

        // Data Grafik Analitik 7 Hari Terakhir
        $chartLabels = [];
        $chartDataTepat = [];
        $chartDataTerlambat = [];
        $chartDataIzin = [];

        for ($i = 6; $i >= 0; $i--) {
            $d = Carbon::now('Asia/Jakarta')->subDays($i)->toDateString();
            $chartLabels[] = Carbon::parse($d)->translatedFormat('d M');
            $chartDataTepat[] = Attendance::whereDate('date', $d)->where('status', 'tepat_waktu')->count();
            $chartDataTerlambat[] = Attendance::whereDate('date', $d)->where('status', 'terlambat')->count();
            $chartDataIzin[] = Attendance::whereDate('date', $d)->whereIn('status', ['sakit', 'izin', 'cuti'])->count();
        }

        return view('admin.dashboard', compact(
            'recentAttendances',
            'totalMagang',
            'todayAttending',
            'todayLate',
            'todayLeave',
            'todayNotYet',
            'pendingLeaveCount',
            'suspiciousGpsCount',
            'chartLabels',
            'chartDataTepat',
            'chartDataTerlambat',
            'chartDataIzin'
        ));
    }

    /**
     * Halaman Kelola & Data Presensi Peserta Magang
     */
    public function attendances(Request $request)
    {
        $today = Carbon::now('Asia/Jakarta')->toDateString();

        $startDate = $request->input('start_date', $today);
        $endDate = $request->input('end_date', $today);
        $search = $request->input('search');
        $campus = $request->input('campus');

        $query = Attendance::with('user')
            ->whereBetween('date', [$startDate, $endDate]);

        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('asal_instansi_kampus', 'like', "%{$search}%");
            });
        }

        if ($campus) {
            $query->whereHas('user', function ($q) use ($campus) {
                $q->where('asal_instansi_kampus', $campus);
            });
        }

        $attendances = $query->orderBy('date', 'desc')
            ->orderBy('check_in_time', 'asc')
            ->paginate(15)
            ->withQueryString();

        // Daftar Asal Instansi / Kampus unik untuk filter dropdown
        $campusList = User::whereNotNull('asal_instansi_kampus')
            ->where('asal_instansi_kampus', '!=', '')
            ->distinct()
            ->pluck('asal_instansi_kampus');

        return view('admin.attendances', compact(
            'attendances',
            'startDate',
            'endDate',
            'search',
            'campus',
            'campusList'
        ));
    }

    /**
     * Ekspor Laporan Rekap Presensi ke Format CSV
     */
    public function exportCsv(Request $request)
    {
        $today = Carbon::now('Asia/Jakarta')->toDateString();
        $startDate = $request->input('start_date', $today);
        $endDate = $request->input('end_date', $today);
        $search = $request->input('search');
        $campus = $request->input('campus');

        $query = Attendance::with('user')
            ->whereBetween('date', [$startDate, $endDate]);

        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('asal_instansi_kampus', 'like', "%{$search}%");
            });
        }

        if ($campus) {
            $query->whereHas('user', function ($q) use ($campus) {
                $q->where('asal_instansi_kampus', $campus);
            });
        }

        $records = $query->orderBy('date', 'desc')->get();

        $filename = "Rekap_Presensi_Magang_{$startDate}_sampai_{$endDate}.csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($records) {
            $file = fopen('php://output', 'w');
            
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, [
                'No',
                'Tanggal',
                'Nama Peserta',
                'NIM/Username',
                'Asal Instansi/Kampus',
                'Jam Masuk (WIB)',
                'Jarak Masuk (Meter)',
                'Status Kehadiran',
                'Keterlambatan (Menit)',
                'Jam Pulang (WIB)',
                'Jarak Pulang (Meter)',
                'Akurasi GPS (m)',
                'Indikasi Fake GPS',
                'IP Address',
                'Catatan Keamanan',
            ]);

            foreach ($records as $index => $row) {
                fputcsv($file, [
                    $index + 1,
                    $row->date ? $row->date->format('Y-m-d') : '-',
                    $row->user->name ?? '-',
                    $row->user->username ?? '-',
                    $row->user->asal_instansi_kampus ?? '-',
                    $row->check_in_time ? substr($row->check_in_time, 0, 5) : '-',
                    $row->distance_in_meters !== null ? $row->distance_in_meters . ' m' : '-',
                    strtoupper(str_replace('_', ' ', $row->status)),
                    $row->late_minutes ?? 0,
                    $row->check_out_time ? substr($row->check_out_time, 0, 5) : '-',
                    $row->distance_out_meters !== null ? $row->distance_out_meters . ' m' : '-',
                    $row->gps_accuracy !== null ? "±{$row->gps_accuracy}m" : '-',
                    $row->is_mock_location ? 'YA (Mock GPS)' : ($row->is_suspicious ? 'Mencurigakan' : 'Normal'),
                    $row->ip_address ?? '-',
                    $row->security_note ?? '-',
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Tampilan Pengaturan Titik Lokasi Kantor & Jam Kerja
     */
    public function officeLocation()
    {
        $office = OfficeLocation::firstOrCreate(
            ['is_active' => true],
            [
                'location_name' => 'Kantor Induk Dishub DIY',
                'latitude' => -7.79560000,
                'longitude' => 110.36950000,
                'radius_meters' => 100,
                'check_in_cutoff' => '07:30:00',
                'check_out_start_weekday' => '16:00:00',
                'check_out_start_friday' => '15:30:00',
                'check_out_start_weekend' => '16:00:00',
                'is_active' => true,
            ]
        );

        return view('admin.office', compact('office'));
    }

    /**
     * Update Titik & Radius Kantor serta Jam Kerja Operasional
     */
    public function officeLocationUpdate(Request $request)
    {
        $request->validate([
            'location_name' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'radius_meters' => ['required', 'integer', 'min:10', 'max:5000'],
            'check_in_cutoff' => ['required', 'date_format:H:i'],
            'check_out_start_weekday' => ['required', 'date_format:H:i'],
            'check_out_start_friday' => ['required', 'date_format:H:i'],
            'check_out_start_weekend' => ['nullable', 'date_format:H:i'],
        ]);

        $office = OfficeLocation::where('is_active', true)->first();
        if (!$office) {
            $office = new OfficeLocation(['is_active' => true]);
        }

        $office->update([
            'location_name' => $request->location_name,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'radius_meters' => $request->radius_meters,
            'check_in_cutoff' => $request->check_in_cutoff . ':00',
            'check_out_start_weekday' => $request->check_out_start_weekday . ':00',
            'check_out_start_friday' => $request->check_out_start_friday . ':00',
            'check_out_start_weekend' => $request->check_out_start_weekend ? ($request->check_out_start_weekend . ':00') : ($office->check_out_start_weekend ?? '16:00:00'),
        ]);

        return back()->with('success', 'Titik lokasi kantor dan konfigurasi jam kerja operasional berhasil diperbarui.');
    }

    /**
     * Kelola Pengguna (Magang & Admin)
     */
    public function users()
    {
        $users = User::orderBy('role', 'asc')
            ->orderBy('name', 'asc')
            ->paginate(15);

        return view('admin.users', compact('users'));
    }

    /**
     * Tambah Pengguna Baru
     */
    public function userStore(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:100', 'unique:users,username'],
            'email' => ['nullable', 'email', 'unique:users,email'],
            'role' => ['required', 'in:admin,magang'],
            'asal_instansi_kampus' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'role' => $request->role,
            'asal_instansi_kampus' => $request->asal_instansi_kampus,
            'password' => Hash::make($request->password),
            'is_active' => true,
        ]);

        return back()->with('success', 'Pengguna baru berhasil ditambahkan.');
    }

    /**
     * Edit Informasi Biodata User oleh Admin
     */
    public function userUpdate(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:100', Rule::unique('users')->ignore($user->id)],
            'email' => ['nullable', 'email', Rule::unique('users')->ignore($user->id)],
            'role' => ['required', 'in:admin,magang'],
            'asal_instansi_kampus' => ['nullable', 'string', 'max:255'],
        ]);

        $user->update([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'role' => $request->role,
            'asal_instansi_kampus' => $request->asal_instansi_kampus,
        ]);

        return back()->with('success', "Informasi pengguna {$user->name} berhasil diperbarui.");
    }

    /**
     * Reset Password User oleh Admin
     */
    public function userResetPassword(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'password' => ['required', 'string', 'min:6'],
        ], [
            'password.min' => 'Kata sandi baru minimal 6 karakter.'
        ]);

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', "Kata sandi untuk akun {$user->name} ({$user->username}) berhasil direset.");
    }

    /**
     * Toggle Aktif / Nonaktifkan User
     */
    public function userToggleStatus($id)
    {
        $user = User::findOrFail($id);
        
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun sendiri.');
        }

        $user->is_active = !$user->is_active;
        $user->save();

        return back()->with('success', "Status akun {$user->name} berhasil diubah menjadi " . ($user->is_active ? 'Aktif' : 'Non-Aktif') . '.');
    }

    /**
     * Rapor Presensi Individu Peserta Magang
     */
    public function userReport($id)
    {
        $user = User::findOrFail($id);

        $attendances = Attendance::where('user_id', $user->id)
            ->orderBy('date', 'desc')
            ->paginate(20);

        $totalHadirTepat = Attendance::where('user_id', $user->id)->where('status', 'tepat_waktu')->count();
        $totalTerlambat = Attendance::where('user_id', $user->id)->where('status', 'terlambat')->count();
        $totalIzinSakit = Attendance::where('user_id', $user->id)->whereIn('status', ['sakit', 'izin', 'cuti'])->count();
        $totalLateMinutes = Attendance::where('user_id', $user->id)->sum('late_minutes');

        $totalPresensi = $totalHadirTepat + $totalTerlambat + $totalIzinSakit;
        $attendanceRate = $totalPresensi > 0 ? round((($totalHadirTepat + $totalTerlambat) / $totalPresensi) * 100, 1) : 0;

        return view('admin.user_report', compact(
            'user',
            'attendances',
            'totalHadirTepat',
            'totalTerlambat',
            'totalIzinSakit',
            'totalLateMinutes',
            'attendanceRate'
        ));
    }
}
