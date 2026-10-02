<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\OfficeLocation;
use App\Services\GeoService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AttendanceController extends Controller
{
    /**
     * Tampilkan halaman utama presensi mandiri (Mobile-first)
     */
    public function index()
    {
        $user = Auth::user();
        $now = Carbon::now('Asia/Jakarta');
        $today = $now->toDateString();

        $todayAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        $office = OfficeLocation::where('is_active', true)->first();

        // 1. Cek jam kerja operasional dinamis (Jumat vs Hari Biasa)
        $checkOutStartTime = $office ? $office->getCheckOutTimeForCarbon($now) : '16:00:00';
        $checkOutCarbon = Carbon::createFromTimeString($checkOutStartTime, 'Asia/Jakarta');
        
        $isCheckoutTime = $now->greaterThanOrEqualTo($checkOutCarbon);
        $isFriday = $now->isFriday();
        $isWeekend = $now->isWeekend();
        $currentTime = $now->format('H:i');

        // 2. Cek apakah peserta sedang dalam status Izin/Sakit/Cuti yang disetujui hari ini
        $approvedLeave = LeaveRequest::where('user_id', $user->id)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->first();

        return view('presensi.index', compact(
            'todayAttendance',
            'office',
            'currentTime',
            'checkOutStartTime',
            'isCheckoutTime',
            'isFriday',
            'isWeekend',
            'approvedLeave'
        ));
    }

    /**
     * Dapatkan info titik kantor aktif untuk JS client-side
     */
    public function officeInfo()
    {
        $office = OfficeLocation::where('is_active', true)->first();
        if (!$office) {
            return response()->json(['error' => 'Kantor belum dikonfigurasi.'], 404);
        }

        $now = Carbon::now('Asia/Jakarta');

        return response()->json([
            'location_name' => $office->location_name,
            'latitude' => (float) $office->latitude,
            'longitude' => (float) $office->longitude,
            'radius_meters' => (int) $office->radius_meters,
            'check_in_cutoff' => substr($office->check_in_cutoff ?? '07:30:00', 0, 5),
            'check_out_start' => substr($office->getCheckOutTimeForCarbon($now), 0, 5),
            'is_friday' => $now->isFriday(),
        ]);
    }

    /**
     * Process Check-in Presensi dengan Proteksi Security Anti-Fake GPS Hardening
     */
    public function storeCheckIn(Request $request)
    {
        $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'photo' => ['required', 'string'], // Base64 data URL
            'accuracy' => ['nullable', 'numeric'],
            'is_mock' => ['nullable', 'boolean'],
            'device_jitter' => ['nullable', 'numeric'],
        ]);

        $user = Auth::user();
        $now = Carbon::now('Asia/Jakarta');
        $today = $now->toDateString();

        // Check Weekend (Sabtu - Minggu Libur Operasional)
        if ($now->isWeekend()) {
            return response()->json([
                'success' => false,
                'message' => 'Hari Sabtu & Minggu adalah hari libur operasional kantor. Layanan presensi tidak dibuka.'
            ], 422);
        }

        // Security Check 1: Proteksi Anti-Fake GPS & Mock Location Direct Detection
        if ($request->boolean('is_mock')) {
            return response()->json([
                'success' => false,
                'message' => '🛡️ SECURITY ALERT: Fitur Lokasi Palsu / Fake GPS / Mock Location terdeteksi aktif di perangkat Anda! Harap matikan aplikasi Fake GPS untuk dapat presensi.'
            ], 422);
        }

        // Security Check 2: Proteksi Akurasi GPS Absurd (Fake GPS biasanya memberikan akurasi 0.0m atau > 150m)
        $accuracy = $request->input('accuracy') !== null ? (float) $request->input('accuracy') : null;
        $isSuspicious = false;
        $securityNote = null;

        if ($accuracy !== null) {
            if ($accuracy <= 0.05) {
                return response()->json([
                    'success' => false,
                    'message' => '🛡️ SECURITY ALERT: Terdeteksi manipulasi koordinat GPS (Unnatural Zero Variance Accuracy). Harap gunakan GPS resmi bawaan perangkat.'
                ], 422);
            }
            
            if ($accuracy > 150) {
                return response()->json([
                    'success' => false,
                    'message' => "Sinyal GPS terlalu lemah (Akurasi: ±{$accuracy}m). Harap keluar ruangan atau tunggu sinyal GPS lebih akurat."
                ], 422);
            }

            if ($accuracy < 0.5) {
                $isSuspicious = true;
                $securityNote = 'Indikasi Akurasi Terlalu Presisi (Ultra-Static Mock GPS)';
            }
        }

        // 1. Cek apakah user sedang dalam status Izin/Sakit/Cuti yang disetujui hari ini
        $approvedLeave = LeaveRequest::where('user_id', $user->id)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->first();

        if ($approvedLeave) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sedang dalam status ' . strtoupper($approvedLeave->type) . ' yang telah disetujui untuk hari ini. Tidak perlu melakukan presensi.'
            ], 422);
        }

        // 2. Cek apakah user sudah check-in hari ini
        $existing = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        if ($existing && $existing->check_in_time) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan Check-in Masuk hari ini pada pukul ' . substr($existing->check_in_time, 0, 5) . ' WIB.'
            ], 422);
        }

        // 3. Ambil titik kantor aktif
        $office = OfficeLocation::where('is_active', true)->first();
        if (!$office) {
            return response()->json([
                'success' => false,
                'message' => 'Titik lokasi kantor belum dikonfigurasi oleh Admin.'
            ], 422);
        }

        // 4. Kalkulasi Haversine Distance (Server-side authoritative)
        $distance = GeoService::calculateDistance(
            (float) $request->latitude,
            (float) $request->longitude,
            (float) $office->latitude,
            (float) $office->longitude
        );

        if ($distance > $office->radius_meters) {
            return response()->json([
                'success' => false,
                'message' => "Presensi gagal. Anda berada di luar radius kantor! (Jarak Anda: {$distance} meter dari titik kantor, Maksimal: {$office->radius_meters} meter)."
            ], 422);
        }

        // 5. Proses Simpan Foto Base64
        $photoPath = $this->saveBase64Image($request->photo, 'attendances/checkin');

        // 6. Aturan Jam Masuk (Dinamis sesuai pengesahan kantor)
        $checkInTimeStr = $now->format('H:i:s');
        $cutoffTimeStr = $office->check_in_cutoff ?? '07:30:00';
        $cutoffTime = Carbon::createFromTimeString($cutoffTimeStr, 'Asia/Jakarta');
        
        $status = 'tepat_waktu';
        $lateMinutes = 0;

        if ($now->greaterThan($cutoffTime)) {
            $status = 'terlambat';
            $lateMinutes = (int) abs($now->diffInMinutes($cutoffTime));
        }

        // 7. Simpan Ke Database
        $attendanceData = [
            'user_id' => $user->id,
            'date' => $today,
            'check_in_time' => $checkInTimeStr,
            'check_in_photo' => $photoPath,
            'check_in_lat' => $request->latitude,
            'check_in_long' => $request->longitude,
            'distance_in_meters' => $distance,
            'status' => $status,
            'late_minutes' => $lateMinutes,
            'is_mock_location' => false,
            'gps_accuracy' => $accuracy,
            'is_suspicious' => $isSuspicious,
            'security_note' => $securityNote,
            'ip_address' => $request->ip(),
            'user_agent' => substr($request->userAgent() ?? '', 0, 255),
        ];

        if ($existing) {
            $existing->update($attendanceData);
            $attendance = $existing;
        } else {
            $attendance = Attendance::create($attendanceData);
        }

        return response()->json([
            'success' => true,
            'message' => 'Check-in berhasil disimpan! ' . ($status === 'terlambat' ? " (Terlambat {$lateMinutes} menit)" : ' (Tepat waktu)'),
            'data' => [
                'check_in_time' => substr($checkInTimeStr, 0, 5),
                'status' => $status,
                'late_minutes' => $lateMinutes,
                'distance' => $distance,
                'photo_url' => asset('storage/' . $photoPath),
            ]
        ]);
    }

    /**
     * Process Check-out Presensi dengan Fleksibilitas Jam Pulang (Jumat Beda Jam)
     */
    public function storeCheckOut(Request $request)
    {
        $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'photo' => ['required', 'string'],
            'accuracy' => ['nullable', 'numeric'],
            'is_mock' => ['nullable', 'boolean'],
        ]);

        $user = Auth::user();
        $now = Carbon::now('Asia/Jakarta');
        $today = $now->toDateString();

        // Check Weekend (Sabtu - Minggu Libur Operasional)
        if ($now->isWeekend()) {
            return response()->json([
                'success' => false,
                'message' => 'Hari Sabtu & Minggu adalah hari libur operasional kantor. Layanan presensi tidak dibuka.'
            ], 422);
        }

        // Security Check 1: Proteksi Anti-Fake GPS
        if ($request->boolean('is_mock')) {
            return response()->json([
                'success' => false,
                'message' => '🛡️ SECURITY ALERT: Fitur Fake GPS / Mock Location terdeteksi di perangkat Anda!'
            ], 422);
        }

        $accuracy = $request->input('accuracy') !== null ? (float) $request->input('accuracy') : null;

        // 1. Validasi user sudah check-in & belum check-out
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        if (!$attendance || !$attendance->check_in_time) {
            return response()->json([
                'success' => false,
                'message' => 'Presensi gagal. Anda belum melakukan Check-in Masuk hari ini.'
            ], 422);
        }

        if ($attendance->check_out_time) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan Check-out Pulang hari ini pada pukul ' . substr($attendance->check_out_time, 0, 5) . ' WIB.'
            ], 422);
        }

        // 2. Validasi waktu minimal kepulangan dinamis (Fleksibilitas Operasional Jumat vs Hari Lain)
        $office = OfficeLocation::where('is_active', true)->first();
        if (!$office) {
            return response()->json([
                'success' => false,
                'message' => 'Titik lokasi kantor belum dikonfigurasi.'
            ], 422);
        }

        $requiredCheckOutStr = $office->getCheckOutTimeForCarbon($now);
        $requiredCheckOutCarbon = Carbon::createFromTimeString($requiredCheckOutStr, 'Asia/Jakarta');

        if ($now->lessThan($requiredCheckOutCarbon)) {
            $dayLabel = $now->isFriday() ? 'Hari Jumat' : 'Hari Kerja';
            $formattedTime = substr($requiredCheckOutStr, 0, 5);
            return response()->json([
                'success' => false,
                'message' => "Presensi pulang belum dibuka. Jam pulang minimal {$dayLabel} adalah pukul {$formattedTime} WIB."
            ], 422);
        }

        // 3. Validasi Geofencing Kantor
        $distance = GeoService::calculateDistance(
            (float) $request->latitude,
            (float) $request->longitude,
            (float) $office->latitude,
            (float) $office->longitude
        );

        if ($distance > $office->radius_meters) {
            return response()->json([
                'success' => false,
                'message' => "Presensi gagal. Anda berada di luar radius kantor! (Jarak Anda: {$distance} meter dari titik kantor)."
            ], 422);
        }

        // 4. Proses Simpan Foto Base64
        $photoPath = $this->saveBase64Image($request->photo, 'attendances/checkout');

        // 5. Update Record Attendance
        $checkOutTimeStr = $now->format('H:i:s');
        $attendance->update([
            'check_out_time' => $checkOutTimeStr,
            'check_out_photo' => $photoPath,
            'check_out_lat' => $request->latitude,
            'check_out_long' => $request->longitude,
            'distance_out_meters' => $distance,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Check-out berhasil disimpan! Terima kasih atas kerja keras hari ini.',
            'data' => [
                'check_out_time' => substr($checkOutTimeStr, 0, 5),
                'distance' => $distance,
                'photo_url' => asset('storage/' . $photoPath),
            ]
        ]);
    }

    /**
     * Helper untuk konversi dan penyimpanan string image Base64 ke file fisik
     */
    private function saveBase64Image(string $base64Image, string $folder): string
    {
        if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
            $base64Image = substr($base64Image, strpos($base64Image, ',') + 1);
            $extension = strtolower($type[1]);
            if ($extension === 'jpeg') {
                $extension = 'jpg';
            }
        } else {
            $extension = 'jpg';
        }

        $base64Image = str_replace(' ', '+', $base64Image);
        $imageData = base64_decode($base64Image);

        if ($imageData === false) {
            throw new \Exception('Dekode foto Base64 gagal.');
        }

        $filename = Str::random(20) . '_' . time() . '.' . $extension;
        $path = $folder . '/' . $filename;

        Storage::disk('public')->put($path, $imageData);

        return $path;
    }
}
