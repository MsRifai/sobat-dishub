@extends('layouts.app')

@section('title', 'Rapor Presensi Individu - Admin SOBAT Dishub')

@section('content')
<div class="space-y-6">

    <!-- Back & Header Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.users') }}" class="w-10 h-10 rounded-2xl bg-white border border-slate-200 flex items-center justify-center text-slate-600 hover:text-slate-900 shadow-sm transition">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Rapor Presensi Individu</h1>
                <p class="text-xs text-slate-500 font-semibold mt-0.5">Laporan rekapitulasi performa kehadiran peserta magang</p>
            </div>
        </div>

        <a href="{{ route('admin.export-csv', ['search' => $user->username]) }}" class="py-2.5 px-4 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-extrabold shadow-md shadow-emerald-600/20 transition flex items-center gap-2">
            <i class="fa-solid fa-file-csv"></i>
            <span>Ekspor CSV Peserta Ini</span>
        </a>
    </div>

    <!-- User Profile Header Card -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-3xl bg-emerald-500 text-white font-extrabold text-2xl flex items-center justify-center shadow-lg shadow-emerald-500/20 shrink-0">
                {{ strtoupper(substr($user->name, 0, 2)) }}
            </div>
            <div>
                <h3 class="font-extrabold text-xl text-slate-900">{{ $user->name }}</h3>
                <p class="text-xs text-slate-500 font-semibold mt-0.5">{{ $user->asal_instansi_kampus ?? 'Peserta Magang' }}</p>
                <div class="flex items-center gap-2 mt-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-700">
                        Username: {{ $user->username }}
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold {{ $user->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                        {{ $user->is_active ? 'Aktif' : 'Non-Aktif' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Attendance Rate Badge -->
        <div class="bg-slate-50 border border-slate-200 rounded-3xl p-4 text-center min-w-[160px]">
            <span class="text-[10px] font-extrabold uppercase text-slate-400">Tingkat Kehadiran</span>
            <div class="text-3xl font-extrabold text-emerald-600 mt-1">{{ $attendanceRate }}%</div>
            <div class="text-[10px] text-slate-500 font-semibold mt-1">Status Disiplin</div>
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm border-l-4 border-l-emerald-500">
            <span class="text-[10px] font-extrabold uppercase text-slate-400">Tepat Waktu</span>
            <div class="text-2xl font-extrabold text-emerald-600 mt-1">{{ $totalHadirTepat }} Hari</div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm border-l-4 border-l-purple-500">
            <span class="text-[10px] font-extrabold uppercase text-slate-400">Terlambat</span>
            <div class="text-2xl font-extrabold text-purple-600 mt-1">{{ $totalTerlambat }} Hari</div>
            <div class="text-[10px] text-purple-700 font-bold mt-0.5">Total {{ $totalLateMinutes }} Menit</div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm border-l-4 border-l-blue-500">
            <span class="text-[10px] font-extrabold uppercase text-slate-400">Izin / Sakit / Cuti</span>
            <div class="text-2xl font-extrabold text-blue-600 mt-1">{{ $totalIzinSakit }} Hari</div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm border-l-4 border-l-slate-400">
            <span class="text-[10px] font-extrabold uppercase text-slate-400">Total Absens Terdaftar</span>
            <div class="text-2xl font-extrabold text-slate-800 mt-1">{{ $attendances->total() }} Record</div>
        </div>
    </div>

    <!-- Attendance History Table -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
        <h3 class="font-extrabold text-slate-900 text-base flex items-center gap-2">
            <i class="fa-solid fa-clock-rotate-left text-emerald-500"></i> Riwayat Presensi Lengkap
        </h3>

        <div class="overflow-x-auto rounded-2xl border border-slate-200">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50 uppercase tracking-wider text-[10px] text-slate-400 font-extrabold border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3">Check-in Masuk</th>
                        <th class="px-4 py-3">Check-out Pulang</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3">Verifikasi GPS Security</th>
                        <th class="px-4 py-3 text-center">Foto Selfie</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($attendances as $row)
                        <tr class="hover:bg-slate-50/80 transition {{ $row->is_mock_location || $row->is_suspicious ? 'bg-rose-50/40' : '' }}">
                            <td class="px-4 py-3 font-bold text-slate-900 whitespace-nowrap">
                                {{ $row->date ? $row->date->translatedFormat('l, d M Y') : '-' }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if($row->check_in_time)
                                    <div class="font-bold text-slate-900">{{ substr($row->check_in_time, 0, 5) }} WIB</div>
                                    <div class="text-[10px] text-slate-400">Jarak: {{ $row->distance_in_meters ?? 0 }}m</div>
                                @else
                                    <span class="text-slate-400">--:--</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if($row->check_out_time)
                                    <div class="font-bold text-slate-900">{{ substr($row->check_out_time, 0, 5) }} WIB</div>
                                    <div class="text-[10px] text-slate-400">Jarak: {{ $row->distance_out_meters ?? 0 }}m</div>
                                @else
                                    <span class="text-slate-400">--:--</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                @if($row->status === 'tepat_waktu')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-700">
                                        Tepat Waktu
                                    </span>
                                @elseif($row->status === 'terlambat')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-purple-100 text-purple-700">
                                        Terlambat ({{ $row->late_minutes }}m)
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-blue-100 text-blue-700 uppercase">
                                        {{ $row->status }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($row->is_mock_location || $row->is_suspicious)
                                    <span class="px-2 py-0.5 rounded-lg bg-rose-100 text-rose-700 text-[10px] font-extrabold flex items-center gap-1 w-fit">
                                        <i class="fa-solid fa-triangle-exclamation"></i> Mock / Suspicious GPS
                                    </span>
                                    <div class="text-[10px] text-rose-600 mt-0.5">{{ $row->security_note ?? 'Terdeteksi manipulasi koordinat' }}</div>
                                @else
                                    <div class="text-[10px] text-emerald-600 font-bold flex items-center gap-1">
                                        <i class="fa-solid fa-shield-check"></i> Valid GPS (±{{ $row->gps_accuracy ?? 5 }}m)
                                    </div>
                                    <div class="text-[10px] text-slate-400 font-mono">IP: {{ $row->ip_address ?? '-' }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1">
                                    @if($row->check_in_photo)
                                        <a href="{{ asset('storage/' . $row->check_in_photo) }}" target="_blank">
                                            <img src="{{ asset('storage/' . $row->check_in_photo) }}" class="w-8 h-8 rounded-lg object-cover border border-emerald-500 hover:scale-110 transition">
                                        </a>
                                    @endif
                                    @if($row->check_out_photo)
                                        <a href="{{ asset('storage/' . $row->check_out_photo) }}" target="_blank">
                                            <img src="{{ asset('storage/' . $row->check_out_photo) }}" class="w-8 h-8 rounded-lg object-cover border border-blue-500 hover:scale-110 transition">
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-400">
                                <i class="fa-solid fa-calendar-xmark text-3xl mb-2"></i>
                                <div>Belum ada riwayat presensi tercatat untuk peserta ini.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $attendances->links() }}
        </div>
    </div>

</div>
@endsection
