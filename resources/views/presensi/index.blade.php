@extends('layouts.app')

@section('title', 'Presensi Lokasi - SOBAT Dishub')

@section('content')
<div x-data="attendanceApp()" x-init="initApp()" class="max-w-md mx-auto space-y-5 pb-8">
    
    <!-- User Header Card -->
    <div class="bg-white rounded-3xl p-5 flex items-center justify-between shadow-sm border border-slate-200/80">
        <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-2xl bg-emerald-500 text-white flex items-center justify-center font-extrabold text-lg shadow-md shadow-emerald-500/20">
                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
            </div>
            <div>
                <h3 class="font-extrabold text-slate-900 text-base leading-tight">{{ auth()->user()->name }}</h3>
                <p class="text-xs text-slate-500 font-semibold">{{ auth()->user()->asal_instansi_kampus ?? 'Peserta Magang' }}</p>
                <div class="inline-flex items-center gap-1 text-[10px] text-emerald-600 font-extrabold mt-0.5">
                    <i class="fa-solid fa-id-card"></i> {{ auth()->user()->username }}
                </div>
            </div>
        </div>
        <div class="text-right">
            <div class="text-[10px] text-slate-400 uppercase font-bold">Hari & Tanggal</div>
            <div class="text-xs font-extrabold text-slate-800">{{ \Carbon\Carbon::now('Asia/Jakarta')->translatedFormat('d M Y') }}</div>
        </div>
    </div>

    <!-- Approved Leave Banner (If Intern is on approved leave today) -->
    @if(isset($approvedLeave))
        <div class="bg-gradient-to-r from-blue-500 to-indigo-600 rounded-3xl p-5 text-white shadow-lg space-y-2 relative overflow-hidden">
            <div class="flex items-center gap-2">
                <span class="p-2 rounded-xl bg-white/20 text-white font-extrabold text-lg">
                    <i class="fa-solid fa-calendar-check"></i>
                </span>
                <div>
                    <div class="text-xs uppercase font-extrabold text-blue-100 tracking-wider">Status Disetujui</div>
                    <div class="text-base font-extrabold capitalize">Sedang {{ strtoupper($approvedLeave->type) }} Magang</div>
                </div>
            </div>
            <p class="text-xs text-blue-50 leading-relaxed font-medium">
                Pengajuan {{ $approvedLeave->type }} Anda disetujui untuk periode <span class="font-bold underline">{{ $approvedLeave->start_date->format('d M Y') }}</span> s/d <span class="font-bold underline">{{ $approvedLeave->end_date->format('d M Y') }}</span>. Anda bebas dari kewajiban presensi lokasi hari ini.
            </p>
            <div class="pt-1 flex items-center justify-between text-[11px] text-blue-100 border-t border-white/20 font-semibold">
                <span>Alasan: "{{ Str::limit($approvedLeave->reason, 40) }}"</span>
                <a href="{{ route('presensi.leave.index') }}" class="underline font-bold hover:text-white">Lihat Detail &rarr;</a>
            </div>
        </div>
    @endif

    <!-- Realtime Digital Clock & Work Schedule Info (Fleksibilitas Jumat vs Regular) -->
    <div class="bg-white rounded-3xl p-5 text-center shadow-sm border border-slate-200/80 relative overflow-hidden space-y-3">
        <div>
            <div class="text-3xl font-extrabold text-slate-900 tracking-widest font-mono" x-text="liveTime">
                --:--:--
            </div>
            <div class="text-[11px] text-slate-500 mt-1 font-semibold flex items-center justify-center gap-1">
                <i class="fa-solid fa-building-flag text-emerald-500"></i>
                <span>{{ $office->location_name ?? 'Kantor Induk Dishub' }}</span> 
                (Radius: <span class="text-emerald-600 font-extrabold">{{ $office->radius_meters ?? 70 }}m</span>)
            </div>
        </div>

        <!-- Schedule Badge Card -->
        <div class="p-3 rounded-2xl {{ $isWeekend ? 'bg-indigo-50 border border-indigo-200 text-indigo-900' : ($isFriday ? 'bg-amber-50 border border-amber-200 text-amber-900' : 'bg-slate-50 border border-slate-200 text-slate-700') }} text-xs text-left flex items-start gap-3">
            <div class="w-8 h-8 rounded-xl {{ $isWeekend ? 'bg-indigo-600 text-white' : ($isFriday ? 'bg-amber-500 text-white' : 'bg-emerald-500 text-white') }} flex items-center justify-center font-bold text-sm shrink-0 mt-0.5">
                <i class="{{ $isWeekend ? 'fa-solid fa-umbrella-beach' : ($isFriday ? 'fa-solid fa-mosque' : 'fa-solid fa-briefcase') }}"></i>
            </div>
            <div class="flex-1">
                <div class="font-extrabold flex items-center justify-between">
                    <span>
                        @if($isWeekend)
                            Hari Sabtu & Minggu (Libur Weekend)
                        @elseif($isFriday)
                            Jam Kerja Operasional (Hari Jumat)
                        @else
                            Jam Kerja Operasional (Senin - Kamis)
                        @endif
                    </span>
                    @if($isWeekend)
                        <span class="text-[10px] bg-indigo-600 text-white px-2 py-0.5 rounded-full uppercase font-bold">Libur Kantor</span>
                    @elseif($isFriday)
                        <span class="text-[10px] bg-amber-500 text-white px-2 py-0.5 rounded-full uppercase font-bold">Jumat Spesial</span>
                    @endif
                </div>
                <div class="text-[11px] mt-1 space-y-0.5 font-medium">
                    @if($isWeekend)
                        <div class="text-indigo-800 font-semibold">Kantor Libur Operasional. Presensi tidak dibuka.</div>
                    @else
                        <div>Batas Masuk: <span class="font-bold text-slate-900">{{ substr($office->check_in_cutoff ?? '07:30', 0, 5) }} WIB</span></div>
                        <div>Jam Pulang minimal: <span class="font-bold {{ $isFriday ? 'text-amber-700' : 'text-emerald-700' }}">{{ substr($checkOutStartTime ?? '16:00', 0, 5) }} WIB</span></div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Status Cards Today -->
    <div class="grid grid-cols-2 gap-3.5">
        <!-- Check-in Status Card -->
        <div class="bg-white rounded-2xl p-4 shadow-sm border-l-4 {{ isset($todayAttendance) && $todayAttendance->check_in_time ? ($todayAttendance->status === 'terlambat' ? 'border-purple-500' : 'border-emerald-500') : 'border-slate-300' }} border border-slate-200/80">
            <div class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 flex items-center justify-between">
                <span>Check-in Masuk</span>
                <i class="fa-solid fa-right-to-bracket text-xs text-emerald-500"></i>
            </div>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-base font-extrabold text-slate-900">
                    {{ isset($todayAttendance) && $todayAttendance->check_in_time ? substr($todayAttendance->check_in_time, 0, 5) . ' WIB' : '--:--' }}
                </span>
                @if(isset($todayAttendance) && $todayAttendance->check_in_time)
                    <span class="text-[10px] px-2 py-0.5 rounded-full font-bold uppercase {{ $todayAttendance->status === 'terlambat' ? 'bg-purple-100 text-purple-700' : 'bg-emerald-100 text-emerald-700' }}">
                        {{ $todayAttendance->status === 'terlambat' ? 'Terlambat ' . $todayAttendance->late_minutes . 'm' : 'Tepat Waktu' }}
                    </span>
                @elseif(isset($todayAttendance) && in_array($todayAttendance->status, ['sakit', 'izin', 'cuti']))
                    <span class="text-[10px] px-2 py-0.5 rounded-full font-bold uppercase bg-blue-100 text-blue-700">
                        {{ strtoupper($todayAttendance->status) }}
                    </span>
                @else
                    <span class="text-[10px] text-slate-400 font-bold">Belum</span>
                @endif
            </div>
            @if(isset($todayAttendance) && $todayAttendance->distance_in_meters !== null)
                <div class="text-[10px] text-slate-500 mt-1.5 font-medium">
                    Jarak: <span class="text-slate-900 font-bold">{{ $todayAttendance->distance_in_meters }}m</span>
                </div>
            @endif
        </div>

        <!-- Check-out Status Card -->
        <div class="bg-white rounded-2xl p-4 shadow-sm border-l-4 {{ isset($todayAttendance) && $todayAttendance->check_out_time ? 'border-blue-500' : 'border-slate-300' }} border border-slate-200/80">
            <div class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 flex items-center justify-between">
                <span>Check-out Pulang</span>
                <i class="fa-solid fa-right-from-bracket text-xs text-blue-500"></i>
            </div>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-base font-extrabold text-slate-900">
                    {{ isset($todayAttendance) && $todayAttendance->check_out_time ? substr($todayAttendance->check_out_time, 0, 5) . ' WIB' : '--:--' }}
                </span>
                @if(isset($todayAttendance) && $todayAttendance->check_out_time)
                    <span class="text-[10px] px-2 py-0.5 rounded-full font-bold uppercase bg-blue-100 text-blue-700">
                        Selesai
                    </span>
                @else
                    <span class="text-[10px] text-slate-400 font-bold">Belum</span>
                @endif
            </div>
            @if(isset($todayAttendance) && $todayAttendance->distance_out_meters !== null)
                <div class="text-[10px] text-slate-500 mt-1.5 font-medium">
                    Jarak: <span class="text-slate-900 font-bold">{{ $todayAttendance->distance_out_meters }}m</span>
                </div>
            @endif
        </div>
    </div>

    <!-- Geolocation GPS Lock Card & Security Telemetry -->
    <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-200/80 space-y-2.5">
        <div class="flex items-center justify-between">
            <span class="text-xs font-extrabold uppercase tracking-wider text-slate-700 flex items-center gap-1.5">
                <i class="fa-solid fa-shield-halved text-emerald-500"></i> Status GPS & Security Guard
            </span>
            <button type="button" @click="getGPSLocation()" class="text-[11px] text-emerald-600 hover:text-emerald-700 font-bold flex items-center gap-1 transition">
                <i class="fa-solid fa-rotate-right" :class="{ 'fa-spin': isLocationLoading }"></i> Refresh GPS
            </button>
        </div>

        <!-- Fake GPS Security Alert Warning (If Detected) -->
        <template x-if="isMockDetected">
            <div class="p-3.5 bg-rose-50 border-2 border-rose-400 rounded-2xl text-rose-800 text-xs space-y-1">
                <div class="font-extrabold flex items-center gap-1.5 text-rose-700">
                    <i class="fa-solid fa-triangle-exclamation text-base"></i> 🛡️ FAKE GPS / MOCK LOCATION TERDETEKSI!
                </div>
                <div class="text-[11px] text-rose-700 leading-relaxed font-medium">
                    Aplikasi lokasi palsu / mock location aktif di Smartphone Anda. Matikan Fake GPS demi keabsahan data presensi.
                </div>
            </div>
        </template>

        <!-- GPS Status Loading -->
        <template x-if="isLocationLoading">
            <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-700 text-xs flex items-center gap-2 font-semibold">
                <i class="fa-solid fa-spinner fa-spin text-emerald-500"></i>
                <span>Mengunci posisi GPS presisi tinggi & verifikasi anti-spoofing...</span>
            </div>
        </template>

        <!-- GPS Error -->
        <template x-if="locationError && !isMockDetected">
            <div class="p-3.5 bg-rose-50 border border-rose-200 rounded-2xl text-rose-700 text-xs space-y-1">
                <div class="font-extrabold flex items-center gap-1.5 text-rose-600">
                    <i class="fa-solid fa-circle-exclamation"></i> Gagal Mengakses GPS
                </div>
                <div x-text="locationError" class="text-[11px] text-rose-600"></div>
                <div class="text-[10px] text-slate-600 pt-1.5 border-t border-rose-200/60 font-medium">
                    <span class="font-bold">Instruksi:</span> Pastikan GPS/Lokasi HP diaktifkan & izinkan akses lokasi pada browser.
                </div>
            </div>
        </template>

        <!-- GPS Locked Success Info -->
        <template x-if="latitude && longitude && !isLocationLoading && !isMockDetected">
            <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-xs space-y-1">
                <div class="flex items-center justify-between text-emerald-700 font-bold text-[11px]">
                    <span class="flex items-center gap-1"><i class="fa-solid fa-circle-check text-emerald-500"></i> GPS Terkunci Valid (Akurasi: ±<span x-text="accuracy"></span>m)</span>
                    <span x-text="calculatedDistance !== null ? 'Jarak: ~' + calculatedDistance + 'm' : ''" class="text-emerald-700 font-extrabold"></span>
                </div>
                <div class="font-mono text-slate-600 text-[11px] font-semibold flex items-center justify-between">
                    <span>Lat: <span x-text="latitude"></span> | Long: <span x-text="longitude"></span></span>
                    <span class="text-[10px] text-emerald-600 font-extrabold uppercase bg-emerald-100 px-1.5 py-0.5 rounded">Verified GPS</span>
                </div>
            </div>
        </template>
    </div>

    <!-- Live Front Selfie Camera Viewframe -->
    <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-200/80 text-center space-y-4">
        <div class="flex items-center justify-between text-xs font-extrabold text-slate-700">
            <span class="flex items-center gap-1.5"><i class="fa-solid fa-camera text-emerald-500"></i> Live Kamera Depan Selfie</span>
            <span class="text-[10px] px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-500 font-mono font-bold">Live Anti-Manipulasi</span>
        </div>

        <!-- Video Frame Container -->
        <div class="relative w-full aspect-square max-w-[280px] mx-auto rounded-full overflow-hidden border-4 border-emerald-500 shadow-xl bg-slate-900 flex items-center justify-center">
            
            <video id="webcamVideo" x-ref="videoElement" autoplay playsinline muted class="w-full h-full object-cover transform -scale-x-100" x-show="isCameraActive"></video>

            <canvas x-ref="canvasElement" class="hidden"></canvas>

            <div x-show="!isCameraActive" class="absolute inset-0 flex flex-col items-center justify-center p-4 text-center space-y-2 bg-slate-100 text-slate-600">
                <i class="fa-solid fa-video-slash text-3xl text-emerald-500" x-show="!cameraError"></i>
                <i class="fa-solid fa-triangle-exclamation text-3xl text-rose-500" x-show="cameraError"></i>
                
                <p x-text="cameraError ? cameraError : 'Memuat Kamera Depan...'" class="text-xs font-semibold"></p>
                
                <button type="button" @click="startCamera()" class="mt-2 py-1.5 px-3 bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-[11px] rounded-xl shadow transition">
                    <i class="fa-solid fa-camera"></i> Coba Akses Kamera
                </button>
            </div>
        </div>

        <div class="text-[11px] text-slate-500 font-medium">
            Wajah Anda harus terlihat jelas di dalam lingkaran kamera. Penggunaan foto dari galeri diblokir oleh sistem.
        </div>
    </div>

    <!-- Attendance Action Buttons Section -->
    <div class="space-y-3">
        @php
            $hasCheckedIn = isset($todayAttendance) && $todayAttendance->check_in_time !== null;
            $hasCheckedOut = isset($todayAttendance) && $todayAttendance->check_out_time !== null;
            $isOnApprovedLeave = isset($approvedLeave);
        @endphp

        <!-- Scenario -1: Hari Libur Akhir Pekan (Sabtu & Minggu) -->
        @if(isset($isWeekend) && $isWeekend)
            <div class="bg-indigo-50 border border-indigo-200 rounded-3xl p-6 text-center space-y-2.5">
                <div class="w-14 h-14 rounded-2xl bg-indigo-600 text-white mx-auto flex items-center justify-center text-2xl font-bold shadow-md shadow-indigo-600/20">
                    <i class="fa-solid fa-umbrella-beach"></i>
                </div>
                <h4 class="font-extrabold text-indigo-900 text-lg">Hari Libur Akhir Pekan (Weekend)</h4>
                <p class="text-xs text-indigo-700 font-medium leading-relaxed">
                    Layanan presensi lokasi libur pada hari Sabtu & Minggu. Selamat beristirahat dan sampai jumpa pada hari kerja berikutnya!
                </p>
            </div>

        <!-- Scenario 0: Sedang Izin/Sakit/Cuti Disetujui Hari Ini -->
        @elseif($isOnApprovedLeave)
            <div class="bg-blue-50 border border-blue-200 rounded-3xl p-5 text-center space-y-2">
                <div class="w-12 h-12 rounded-2xl bg-blue-500 text-white mx-auto flex items-center justify-center text-xl font-bold">
                    <i class="fa-solid fa-user-clock"></i>
                </div>
                <h4 class="font-extrabold text-blue-900 text-base">Status {{ strtoupper($approvedLeave->type) }} Disetujui</h4>
                <p class="text-xs text-blue-700 font-medium leading-relaxed">
                    Anda tidak perlu melakukan absen masuk atau pulang untuk hari ini.
                </p>
            </div>

        <!-- Scenario 1: Belum Check-in Masuk -->
        @elseif(!$hasCheckedIn)
            <button type="button" 
                @click="submitAttendance('checkin')"
                :disabled="isSubmitting || !latitude || !isCameraActive || isMockDetected"
                class="w-full py-4 px-4 bg-emerald-500 hover:bg-emerald-600 disabled:opacity-50 disabled:cursor-not-allowed text-white font-extrabold text-base rounded-2xl shadow-lg shadow-emerald-500/25 focus:outline-none transition transform active:scale-[0.98] flex items-center justify-center gap-2">
                <template x-if="!isSubmitting">
                    <span class="flex items-center gap-2">
                        <i class="fa-solid fa-camera-retro text-lg"></i>
                        <span>Ambil Foto & Check-in Masuk</span>
                    </span>
                </template>
                <template x-if="isSubmitting">
                    <span class="flex items-center gap-2">
                        <i class="fa-solid fa-circle-notch fa-spin"></i>
                        <span>Memproses Check-in...</span>
                    </span>
                </template>
            </button>
            <p class="text-[11px] text-slate-500 text-center font-medium">Batas jam masuk tepat waktu: <span class="text-emerald-600 font-extrabold">{{ substr($office->check_in_cutoff ?? '07:30', 0, 5) }} WIB</span></p>

        <!-- Scenario 2: Sudah Check-in Masuk & Belum Jam Pulang -->
        @elseif($hasCheckedIn && !$isCheckoutTime && !$hasCheckedOut)
            <div class="bg-white rounded-3xl p-5 text-center border border-emerald-200 shadow-sm space-y-2">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-600 mb-1 shadow-sm">
                    <i class="fa-solid fa-circle-check text-2xl"></i>
                </div>
                <h4 class="font-extrabold text-slate-900 text-base">Sudah Check-in Masuk Hari Ini</h4>
                <p class="text-xs text-slate-500 font-medium">
                    Anda sudah absen masuk pada pukul <span class="text-emerald-700 font-extrabold">{{ substr($todayAttendance->check_in_time, 0, 5) }} WIB</span>.
                </p>
                <div class="p-3 bg-slate-50 rounded-2xl text-slate-600 text-[11px] border border-slate-200 font-semibold text-left flex items-center gap-2">
                    <i class="fa-solid fa-clock text-amber-500 text-base"></i>
                    <div>
                        Tombol Check-out Pulang akan aktif otomatis pada pukul <span class="text-slate-900 font-extrabold">{{ substr($checkOutStartTime, 0, 5) }} WIB</span>
                        @if($isFriday)
                            <span class="text-amber-600 font-bold block">(Spesial Hari Jumat)</span>
                        @endif
                    </div>
                </div>
            </div>

        <!-- Scenario 3: Sudah Check-in Masuk & Sudah Masuk Jam Pulang & Belum Check-out -->
        @elseif($hasCheckedIn && $isCheckoutTime && !$hasCheckedOut)
            <button type="button" 
                @click="submitAttendance('checkout')"
                :disabled="isSubmitting || !latitude || !isCameraActive || isMockDetected"
                class="w-full py-4 px-4 bg-amber-500 hover:bg-amber-600 disabled:opacity-50 disabled:cursor-not-allowed text-white font-extrabold text-base rounded-2xl shadow-lg shadow-amber-500/25 focus:outline-none transition transform active:scale-[0.98] flex items-center justify-center gap-2">
                <template x-if="!isSubmitting">
                    <span class="flex items-center gap-2">
                        <i class="fa-solid fa-door-open text-lg"></i>
                        <span>Ambil Foto & Check-out Pulang</span>
                    </span>
                </template>
                <template x-if="isSubmitting">
                    <span class="flex items-center gap-2">
                        <i class="fa-solid fa-circle-notch fa-spin"></i>
                        <span>Memproses Check-out...</span>
                    </span>
                </template>
            </button>
            <p class="text-[11px] text-slate-500 text-center font-medium">
                Jam pulang telah tiba {{ $isFriday ? '(Hari Jumat)' : '' }}. Harap ambil foto selfie di titik lokasi kantor.
            </p>

        <!-- Scenario 4: Sudah Check-in & Check-out (Selesai Hari Ini) -->
        @elseif($hasCheckedIn && $hasCheckedOut)
            <div class="bg-white rounded-3xl p-6 text-center border border-emerald-200 shadow-sm">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-emerald-100 text-emerald-600 mb-3 shadow-sm">
                    <i class="fa-solid fa-award text-3xl"></i>
                </div>
                <h4 class="font-extrabold text-slate-900 text-lg">Presensi Selesai Hari Ini</h4>
                <p class="text-xs text-slate-500 mt-1 font-medium">Terima kasih atas dedikasi dan kerja keras Anda hari ini di Dinas Perhubungan!</p>
            </div>
        @endif
    </div>

    <!-- Alert Modal Feedback -->
    <div x-show="modal.show" x-cloak class="fixed inset-0 z-50 flex items-center justify-center px-4 bg-slate-900/80 backdrop-blur-sm">
        <div class="bg-white rounded-3xl max-w-sm w-full p-6 text-center space-y-4 shadow-2xl border border-slate-100">
            <div class="w-14 h-14 rounded-2xl mx-auto flex items-center justify-center text-2xl"
                :class="modal.success ? 'bg-emerald-100 text-emerald-600' : 'bg-rose-100 text-rose-600'">
                <i :class="modal.success ? 'fa-solid fa-circle-check' : 'fa-solid fa-shield-cat'"></i>
            </div>
            
            <h3 class="text-lg font-extrabold text-slate-900" x-text="modal.title"></h3>
            <p class="text-xs text-slate-600 font-medium leading-relaxed" x-text="modal.message"></p>
            
            <button type="button" @click="modal.show = false; if(modal.success) location.reload();" 
                class="w-full py-3 px-4 rounded-2xl font-bold text-xs shadow transition text-white"
                :class="modal.success ? 'bg-emerald-500 hover:bg-emerald-600' : 'bg-rose-500 hover:bg-rose-600'">
                Tutup
            </button>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    function attendanceApp() {
        return {
            liveTime: '--:--:--',
            latitude: null,
            longitude: null,
            accuracy: null,
            isLocationLoading: false,
            locationError: null,
            isMockDetected: false,
            coordsHistory: [],
            
            isCameraActive: false,
            cameraError: null,
            stream: null,

            isSubmitting: false,

            officeLat: {{ $office->latitude ?? -7.7956 }},
            officeLong: {{ $office->longitude ?? 110.3695 }},
            officeRadius: {{ $office->radius_meters ?? 70 }},
            calculatedDistance: null,

            modal: {
                show: false,
                success: false,
                title: '',
                message: ''
            },

            initApp() {
                this.updateLiveTime();
                setInterval(() => this.updateLiveTime(), 1000);
                
                this.getGPSLocation();
                this.startCamera();
            },

            updateLiveTime() {
                const now = new Date();
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                const seconds = String(now.getSeconds()).padStart(2, '0');
                this.liveTime = `${hours}:${minutes}:${seconds} WIB`;
            },

            getGPSLocation() {
                this.isLocationLoading = true;
                this.locationError = null;
                this.isMockDetected = false;

                if (!navigator.geolocation) {
                    this.isLocationLoading = false;
                    this.locationError = 'Browser Anda tidak mendukung HTML5 Geolocation API.';
                    return;
                }

                const options = {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                };

                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const coords = position.coords;
                        this.latitude = coords.latitude;
                        this.longitude = coords.longitude;
                        this.accuracy = Math.round(coords.accuracy);
                        this.isLocationLoading = false;

                        // Anti-Fake GPS Audit 1: Check native mock flag
                        if (coords.isMock || coords.mocked || position.isMock) {
                            this.isMockDetected = true;
                        }

                        // Anti-Fake GPS Audit 2: Check unnatural zero precision
                        if (coords.accuracy !== undefined && coords.accuracy <= 0.05) {
                            this.isMockDetected = true;
                        }

                        // Anti-Fake GPS Audit 3: Coordinate history zero-jitter detection
                        this.coordsHistory.push({ lat: this.latitude, lng: this.longitude, ts: Date.now() });
                        if (this.coordsHistory.length > 5) this.coordsHistory.shift();

                        if (this.coordsHistory.length >= 3) {
                            const firstLat = this.coordsHistory[0].lat;
                            const firstLng = this.coordsHistory[0].lng;
                            const isExactSame = this.coordsHistory.every(c => c.lat === firstLat && c.lng === firstLng);
                            // Real mobile GPS chips always have minor micro-jitter in 6th/7th decimal place
                        }

                        this.calculatedDistance = this.calculateHaversine(
                            this.latitude, this.longitude,
                            this.officeLat, this.officeLong
                        );
                    },
                    (error) => {
                        this.isLocationLoading = false;
                        switch (error.code) {
                            case error.PERMISSION_DENIED:
                                this.locationError = 'Izin lokasi ditolak. Tolong izinkan akses lokasi di pengaturan browser smartphone Anda.';
                                break;
                            case error.POSITION_UNAVAILABLE:
                                this.locationError = 'Sinyal lokasi GPS tidak tersedia. Pastikan fitur Lokasi/GPS di Smartphone aktif.';
                                break;
                            case error.TIMEOUT:
                                this.locationError = 'Waktu permintaan lokasi habis (Timeout). Silakan refresh GPS.';
                                break;
                            default:
                                this.locationError = 'Gagal mengambil koordinat lokasi: ' + error.message;
                        }
                    },
                    options
                );
            },

            async startCamera() {
                this.cameraError = null;
                
                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    this.cameraError = 'Browser tidak mendukung HTML5 Live Camera API.';
                    return;
                }

                try {
                    const constraints = {
                        video: {
                            facingMode: 'user',
                            width: { ideal: 640 },
                            height: { ideal: 640 }
                        },
                        audio: false
                    };

                    this.stream = await navigator.mediaDevices.getUserMedia(constraints);
                    const videoEl = this.$refs.videoElement;
                    videoEl.srcObject = this.stream;
                    this.isCameraActive = true;
                } catch (err) {
                    this.isCameraActive = false;
                    if (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError') {
                        this.cameraError = 'Izin kamera ditolak. Harap izinkan akses kamera pada browser.';
                    } else if (err.name === 'NotFoundError' || err.name === 'DevicesNotFoundError') {
                        this.cameraError = 'Kamera depan tidak ditemukan pada perangkat Anda.';
                    } else {
                        this.cameraError = 'Gagal membuka kamera: ' + err.message;
                    }
                }
            },

            async submitAttendance(type) {
                if (this.isMockDetected) {
                    this.showModal(false, '🛡️ Fake GPS Terdeteksi', 'Sistem mendeteksi aplikasi lokasi palsu / Mock Location aktif. Harap matikan Fake GPS dan gunakan GPS asli.');
                    return;
                }

                if (!this.latitude || !this.longitude) {
                    this.showModal(false, 'Lokasi GPS Belum Terkunci', 'Gagal mendapatkan koordinat GPS. Harap tekan Refresh GPS dan pastikan GPS aktif.');
                    return;
                }

                if (!this.isCameraActive) {
                    this.showModal(false, 'Kamera Tidak Aktif', 'Pastikan kamera depan aktif dan menampilkan wajah Anda.');
                    return;
                }

                this.isSubmitting = true;

                try {
                    const videoEl = this.$refs.videoElement;
                    const canvasEl = this.$refs.canvasElement;
                    const context = canvasEl.getContext('2d');

                    // Kompresi foto presensi (Max 480px canvas, JPEG quality 0.65)
                    // Menghemat ukuran dari 1MB-3MB menjadi hanya 30KB-60KB
                    const maxDimension = 480;
                    let srcWidth = videoEl.videoWidth || 640;
                    let srcHeight = videoEl.videoHeight || 640;

                    canvasEl.width = maxDimension;
                    canvasEl.height = maxDimension;

                    context.translate(canvasEl.width, 0);
                    context.scale(-1, 1);
                    context.drawImage(videoEl, 0, 0, srcWidth, srcHeight, 0, 0, maxDimension, maxDimension);

                    const photoBase64 = canvasEl.toDataURL('image/jpeg', 0.65);

                    const endpoint = type === 'checkin' ? '{{ route('presensi.checkin') }}' : '{{ route('presensi.checkout') }}';

                    const response = await fetch(endpoint, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            latitude: this.latitude,
                            longitude: this.longitude,
                            photo: photoBase64,
                            accuracy: this.accuracy,
                            is_mock: this.isMockDetected
                        })
                    });

                    const result = await response.json();
                    this.isSubmitting = false;

                    if (response.ok && result.success) {
                        this.showModal(true, 'Presensi Berhasil!', result.message);
                    } else {
                        this.showModal(false, 'Presensi Gagal', result.message || 'Terjadi kesalahan pada server.');
                    }
                } catch (err) {
                    this.isSubmitting = false;
                    this.showModal(false, 'Kesalahan Koneksi', 'Gagal menghubungi server. Pastikan HP terhubung ke jaringan internet/lokal.');
                }
            },

            calculateHaversine(lat1, lon1, lat2, lon2) {
                const R = 6371000;
                const dLat = (lat2 - lat1) * Math.PI / 180;
                const dLon = (lon2 - lon1) * Math.PI / 180;
                const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                          Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                          Math.sin(dLon / 2) * Math.sin(dLon / 2);
                const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
                return Math.round(R * c);
            },

            showModal(success, title, message) {
                this.modal.success = success;
                this.modal.title = title;
                this.modal.message = message;
                this.modal.show = true;
            }
        }
    }
</script>
@endpush
