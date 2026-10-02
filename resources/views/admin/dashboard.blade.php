@extends('layouts.app')

@section('title', 'Dashboard Rekap Presensi - Admin SOBAT Dishub')

@section('content')
<div class="space-y-6">

    <!-- Page Header Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Dashboard Rekapitulasi Presensi</h1>
            <p class="text-xs text-slate-500 font-semibold mt-1">Pemantauan kehadiran peserta magang & verifikasi keamanan lokasi real-time</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.leave.index') }}" class="py-2.5 px-4 rounded-2xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-extrabold shadow-md shadow-amber-500/20 transition flex items-center gap-2">
                <i class="fa-solid fa-file-signature"></i>
                <span>Pengajuan Izin ({{ $pendingLeaveCount }})</span>
            </a>
            <a href="{{ route('admin.export-csv', request()->query()) }}" class="py-2.5 px-4 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-extrabold shadow-md shadow-emerald-600/20 transition flex items-center gap-2">
                <i class="fa-solid fa-file-excel"></i>
                <span>Ekspor CSV</span>
            </a>
        </div>
    </div>

    <!-- Security Warning Alert (If Suspicious / Fake GPS Detected Today) -->
    @if($suspiciousGpsCount > 0)
        <div class="p-4 bg-rose-50 border-2 border-rose-400 rounded-3xl text-rose-900 text-xs flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-rose-500 text-white flex items-center justify-center text-lg font-bold shrink-0">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <div>
                    <h4 class="font-extrabold text-rose-900 text-sm">🛡️ SECURITY AUDIT ALERT ({{ $suspiciousGpsCount }} Kasus Hari Ini)</h4>
                    <p class="text-[11px] text-rose-700 font-medium">Terdeteksi presensi dengan indikasi Fake GPS / Mock Location / Akurasi Absurd pada data presensi hari ini.</p>
                </div>
            </div>
            <a href="#rekapTable" class="py-2 px-3 bg-rose-600 text-white text-[11px] font-extrabold rounded-xl hover:bg-rose-700 transition shrink-0">
                Periksa Data &darr;
            </a>
        </div>
    @endif

    <!-- Metric KPI Cards Summary -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        <!-- Total Magang -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Total Magang Aktif</span>
            <div class="text-2xl font-extrabold text-slate-900 mt-1">{{ $totalMagang }}</div>
            <div class="text-[10px] text-slate-400 font-medium mt-1">Peserta terdaftar</div>
        </div>

        <!-- Hadir Tepat Waktu -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm border-l-4 border-l-emerald-500">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-600">Hadir Hari Ini</span>
            <div class="text-2xl font-extrabold text-emerald-600 mt-1">{{ $todayAttending }}</div>
            <div class="text-[10px] text-slate-500 font-medium mt-1">Absen fisik</div>
        </div>

        <!-- Terlambat -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm border-l-4 border-l-purple-500">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-purple-600">Terlambat Hari Ini</span>
            <div class="text-2xl font-extrabold text-purple-600 mt-1">{{ $todayLate }}</div>
            <div class="text-[10px] text-slate-500 font-medium mt-1">Check-in > cutoff</div>
        </div>

        <!-- Izin / Sakit / Cuti -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm border-l-4 border-l-blue-500">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-blue-600">Izin / Sakit / Cuti</span>
            <div class="text-2xl font-extrabold text-blue-600 mt-1">{{ $todayLeave }}</div>
            <div class="text-[10px] text-slate-500 font-medium mt-1">Disetujui hari ini</div>
        </div>

        <!-- Belum Presensi -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm border-l-4 border-l-amber-500">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-amber-600">Belum Presensi</span>
            <div class="text-2xl font-extrabold text-amber-600 mt-1">{{ $todayNotYet }}</div>
            <div class="text-[10px] text-slate-500 font-medium mt-1">Belum check-in</div>
        </div>
    </div>

    <!-- Analytics Chart Card (Tren Kehadiran 7 Hari Terakhir) -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <h3 class="font-extrabold text-slate-900 text-base flex items-center gap-2">
                    <i class="fa-solid fa-chart-line text-emerald-500"></i> Analitik Tren Kehadiran (7 Hari Terakhir)
                </h3>
                <p class="text-[11px] text-slate-500 font-medium">Grafik komparasi statistik tepat waktu, keterlambatan, dan izin</p>
            </div>
        </div>

        <div class="relative h-64 w-full">
            <canvas id="attendanceTrendChart"></canvas>
        </div>
    </div>

    <!-- Aktivitas Presensi Terbaru Hari Ini -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
            <div>
                <h3 class="font-extrabold text-slate-900 text-base flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left text-emerald-500"></i> Aktivitas Presensi Terkini (Hari Ini)
                </h3>
                <p class="text-[11px] text-slate-500 font-medium">Data presensi terbaru yang baru saja dicatat oleh peserta magang hari ini</p>
            </div>
            <a href="{{ route('admin.attendances') }}" 
                class="py-2.5 px-4 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-extrabold text-xs rounded-2xl border border-emerald-200 transition flex items-center gap-2 self-start sm:self-auto">
                <i class="fa-solid fa-list-check"></i>
                <span>Kelola Semua Data Presensi &rarr;</span>
            </a>
        </div>

        <div class="overflow-x-auto rounded-2xl border border-slate-200">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50 uppercase tracking-wider text-[10px] text-slate-400 font-extrabold border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3">No</th>
                        <th class="px-4 py-3">Peserta Magang</th>
                        <th class="px-4 py-3">Jam Masuk (WIB)</th>
                        <th class="px-4 py-3">Jam Pulang (WIB)</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3">Keamanan GPS</th>
                        <th class="px-4 py-3 text-right">Rapor & Detail</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($recentAttendances as $index => $row)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-4 py-3 font-bold text-slate-400">
                                {{ $index + 1 }}
                            </td>
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.users.report', $row->user_id) }}" class="font-extrabold text-slate-900 hover:text-emerald-600 transition">
                                    {{ $row->user->name ?? '-' }}
                                </a>
                                <div class="text-[10px] text-slate-500 font-semibold">{{ $row->user->username ?? '-' }} | {{ $row->user->asal_instansi_kampus ?? 'Magang' }}</div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if($row->check_in_time)
                                    <div class="font-bold text-slate-900">{{ substr($row->check_in_time, 0, 5) }}</div>
                                    <div class="text-[10px] text-slate-400">Jarak: {{ $row->distance_in_meters ?? 0 }}m</div>
                                @else
                                    <span class="text-slate-400 font-semibold">--:--</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if($row->check_out_time)
                                    <div class="font-bold text-slate-900">{{ substr($row->check_out_time, 0, 5) }}</div>
                                    <div class="text-[10px] text-slate-400">Jarak: {{ $row->distance_out_meters ?? 0 }}m</div>
                                @else
                                    <span class="text-slate-400 font-semibold">--:--</span>
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
                                @elseif(in_array($row->status, ['sakit', 'izin', 'cuti']))
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-blue-100 text-blue-700 uppercase">
                                        {{ $row->status }}
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-slate-100 text-slate-600">
                                        {{ strtoupper($row->status) }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($row->is_mock_location || $row->is_suspicious)
                                    <span class="px-2 py-0.5 rounded-lg bg-rose-100 text-rose-700 text-[10px] font-extrabold flex items-center gap-1 w-fit">
                                        <i class="fa-solid fa-triangle-exclamation"></i> Mock / Suspicious GPS
                                    </span>
                                @else
                                    <div class="text-[10px] text-emerald-600 font-bold flex items-center gap-1">
                                        <i class="fa-solid fa-shield-check"></i> Authentic GPS
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <a href="{{ route('admin.users.report', $row->user_id) }}" class="py-1.5 px-3 bg-slate-100 hover:bg-slate-200 rounded-xl text-slate-700 transition text-[11px] font-bold inline-flex items-center gap-1">
                                    <i class="fa-solid fa-file-user text-emerald-600"></i> Rapor Peserta
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-6 text-center text-slate-400">
                                <i class="fa-solid fa-user-clock text-2xl mb-1"></i>
                                <div>Belum ada aktivitas presensi masuk hari ini.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('attendanceTrendChart').getContext('2d');
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($chartLabels),
                datasets: [
                    {
                        label: 'Hadir Tepat Waktu',
                        data: @json($chartDataTepat),
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        borderWidth: 2.5,
                        fill: true,
                        tension: 0.3
                    },
                    {
                        label: 'Terlambat',
                        data: @json($chartDataTerlambat),
                        borderColor: '#a855f7',
                        backgroundColor: 'rgba(168, 85, 247, 0.1)',
                        borderWidth: 2.5,
                        fill: true,
                        tension: 0.3
                    },
                    {
                        label: 'Izin / Sakit / Cuti',
                        data: @json($chartDataIzin),
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderWidth: 2.5,
                        fill: true,
                        tension: 0.3
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: { family: 'Plus Jakarta Sans', weight: '700', size: 11 }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, font: { family: 'Plus Jakarta Sans' } },
                        grid: { borderDash: [4, 4] }
                    },
                    x: {
                        ticks: { font: { family: 'Plus Jakarta Sans', weight: '600' } },
                        grid: { display: false }
                    }
                }
            }
        });
    });
</script>
@endpush
