@extends('layouts.app')

@section('title', 'Data & Rekapitulasi Presensi - Admin SOBAT Dishub')

@section('content')
<div class="space-y-6">

    <!-- Page Header Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Data Presensi Peserta Magang</h1>
            <p class="text-xs text-slate-500 font-semibold mt-1">Kelola dan pantau seluruh catatan presensi lokasi peserta magang secara real-time</p>
        </div>

        <!-- CSV Export Button -->
        <a href="{{ route('admin.export-csv', ['start_date' => $startDate, 'end_date' => $endDate, 'search' => $search, 'campus' => $campus]) }}" 
            class="py-2.5 px-4 bg-slate-900 hover:bg-slate-800 text-white font-extrabold text-xs rounded-2xl shadow-md transition flex items-center gap-2 self-start sm:self-auto">
            <i class="fa-solid fa-file-csv text-base text-emerald-400"></i>
            <span>Ekspor Data (CSV)</span>
        </a>
    </div>

    <!-- Filter Card -->
    <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('admin.attendances') }}" class="flex flex-col lg:flex-row lg:items-end justify-between gap-4">
            
            <div class="flex flex-wrap items-center gap-3">
                <!-- Start Date -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Dari Tanggal</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" 
                        class="py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <!-- End Date -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Sampai Tanggal</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" 
                        class="py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <!-- Campus Filter -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Instansi / Kampus</label>
                    <select name="campus" class="py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="">-- Semua Kampus --</option>
                        @foreach($campusList as $c)
                            <option value="{{ $c }}" {{ $campus === $c ? 'selected' : '' }}>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="self-end">
                    <button type="submit" class="py-2 px-4 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                        <i class="fa-solid fa-filter"></i>
                        <span>Filter</span>
                    </button>
                </div>
            </div>

            <!-- Search Input -->
            <div class="flex items-center gap-2">
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama, username, atau kampus..."
                    class="py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 w-full sm:w-64">
                <button type="submit" class="py-2 px-4 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <span>Cari</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Attendance Data Table -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
        <div class="overflow-x-auto rounded-2xl border border-slate-200">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50 uppercase tracking-wider text-[10px] text-slate-400 font-extrabold border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3">No</th>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3">Peserta Magang</th>
                        <th class="px-4 py-3">Masuk (WIB)</th>
                        <th class="px-4 py-3">Pulang (WIB)</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3">Verifikasi Security GPS</th>
                        <th class="px-4 py-3 text-center">Aksi & Foto</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($attendances as $index => $row)
                        <tr class="hover:bg-slate-50/80 transition {{ $row->is_mock_location || $row->is_suspicious ? 'bg-rose-50/40' : '' }}">
                            <td class="px-4 py-3 font-bold text-slate-400">
                                {{ $attendances->firstItem() + $index }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap font-bold text-slate-800">
                                {{ $row->date ? $row->date->format('d/m/Y') : '-' }}
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
                                    <div class="text-[10px] text-rose-600 font-medium mt-0.5">{{ $row->security_note ?? 'Terdeteksi manipulasi koordinat' }}</div>
                                @else
                                    <div class="text-[10px] text-emerald-600 font-bold flex items-center gap-1">
                                        <i class="fa-solid fa-shield-check"></i> Authentic GPS 
                                        @if($row->gps_accuracy !== null)
                                            <span class="text-slate-400 font-normal">(±{{ $row->gps_accuracy }}m)</span>
                                        @endif
                                    </div>
                                    <div class="text-[10px] text-slate-400 font-mono">IP: {{ $row->ip_address ?? '127.0.0.1' }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.users.report', $row->user_id) }}" class="p-1.5 bg-slate-100 hover:bg-slate-200 rounded-lg text-slate-600 transition text-[10px] font-bold" title="Rapor Peserta">
                                        <i class="fa-solid fa-file-user"></i>
                                    </a>
                                    @if($row->check_in_photo)
                                        <a href="{{ asset('storage/' . $row->check_in_photo) }}" target="_blank" title="Foto Check-in">
                                            <img src="{{ asset('storage/' . $row->check_in_photo) }}" class="w-8 h-8 rounded-lg object-cover border border-emerald-500 hover:scale-110 transition">
                                        </a>
                                    @endif
                                    @if($row->check_out_photo)
                                        <a href="{{ asset('storage/' . $row->check_out_photo) }}" target="_blank" title="Foto Check-out">
                                            <img src="{{ asset('storage/' . $row->check_out_photo) }}" class="w-8 h-8 rounded-lg object-cover border border-blue-500 hover:scale-110 transition">
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-slate-400">
                                <i class="fa-solid fa-calendar-xmark text-3xl mb-2"></i>
                                <div>Tidak ada data presensi pada rentang tanggal ini.</div>
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
