@extends('layouts.app')

@section('title', 'Kelola Pengajuan Izin & Cuti - Admin SOBAT Dishub')

@section('content')
<div x-data="{ rejectModal: { show: false, actionUrl: '', name: '' } }" class="space-y-6">

    <!-- Page Header Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Kelola Pengajuan Izin, Sakit & Cuti</h1>
            <p class="text-xs text-slate-500 font-semibold mt-1">Review dan berikan persetujuan permohonan ketidakhadiran peserta magang</p>
        </div>
    </div>

    <!-- Alert Notifications -->
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl text-xs font-bold flex items-center gap-2">
            <i class="fa-solid fa-circle-check text-emerald-600 text-base"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('info'))
        <div class="p-4 bg-blue-50 border border-blue-200 text-blue-800 rounded-2xl text-xs font-bold flex items-center gap-2">
            <i class="fa-solid fa-circle-info text-blue-600 text-base"></i>
            <span>{{ session('info') }}</span>
        </div>
    @endif

    <!-- Status Counter Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-extrabold uppercase text-slate-400">Menunggu Persetujuan</span>
                <div class="text-2xl font-extrabold text-amber-600 mt-1">{{ $pendingCount }}</div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl font-bold">
                <i class="fa-solid fa-clock"></i>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-extrabold uppercase text-slate-400">Telah Disetujui</span>
                <div class="text-2xl font-extrabold text-emerald-600 mt-1">{{ $approvedCount }}</div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl font-bold">
                <i class="fa-solid fa-circle-check"></i>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-extrabold uppercase text-slate-400">Ditolak</span>
                <div class="text-2xl font-extrabold text-rose-600 mt-1">{{ $rejectedCount }}</div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl font-bold">
                <i class="fa-solid fa-circle-xmark"></i>
            </div>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm space-y-4">
        <form method="GET" action="{{ route('admin.leave.index') }}" class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            
            <!-- Filter Pills -->
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.leave.index', ['status' => 'all', 'search' => $search]) }}"
                    class="py-2 px-4 rounded-xl text-xs font-extrabold transition {{ $status === 'all' ? 'bg-slate-900 text-white shadow' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    Semua STATUS
                </a>
                <a href="{{ route('admin.leave.index', ['status' => 'pending', 'search' => $search]) }}"
                    class="py-2 px-4 rounded-xl text-xs font-extrabold transition flex items-center gap-1.5 {{ $status === 'pending' ? 'bg-amber-500 text-white shadow' : 'bg-amber-50 text-amber-700 hover:bg-amber-100' }}">
                    <i class="fa-solid fa-clock"></i> Pending ({{ $pendingCount }})
                </a>
                <a href="{{ route('admin.leave.index', ['status' => 'approved', 'search' => $search]) }}"
                    class="py-2 px-4 rounded-xl text-xs font-extrabold transition flex items-center gap-1.5 {{ $status === 'approved' ? 'bg-emerald-600 text-white shadow' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                    <i class="fa-solid fa-circle-check"></i> Disetujui
                </a>
                <a href="{{ route('admin.leave.index', ['status' => 'rejected', 'search' => $search]) }}"
                    class="py-2 px-4 rounded-xl text-xs font-extrabold transition flex items-center gap-1.5 {{ $status === 'rejected' ? 'bg-rose-600 text-white shadow' : 'bg-rose-50 text-rose-700 hover:bg-rose-100' }}">
                    <i class="fa-solid fa-circle-xmark"></i> Ditolak
                </a>
            </div>

            <!-- Search Input -->
            <div class="flex items-center gap-2">
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama / username..."
                    class="py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 w-full sm:w-64">
                <button type="submit" class="py-2 px-4 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl text-xs font-bold transition">
                    Cari
                </button>
            </div>
        </form>

        <!-- Table Data -->
        <div class="overflow-x-auto rounded-2xl border border-slate-200">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50 uppercase tracking-wider text-[10px] text-slate-400 font-extrabold border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3">No</th>
                        <th class="px-4 py-3">Peserta Magang</th>
                        <th class="px-4 py-3">Jenis</th>
                        <th class="px-4 py-3">Periode Tanggal</th>
                        <th class="px-4 py-3">Alasan & Lampiran</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($leaveRequests as $index => $row)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-4 py-3 font-bold text-slate-400">
                                {{ $leaveRequests->firstItem() + $index }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-extrabold text-slate-900">{{ $row->user->name ?? '-' }}</div>
                                <div class="text-[10px] text-slate-500 font-semibold">{{ $row->user->username ?? '-' }} | {{ $row->user->asal_instansi_kampus ?? 'Magang' }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2.5 py-1 rounded-xl text-[10px] font-extrabold uppercase
                                    {{ $row->type === 'sakit' ? 'bg-amber-100 text-amber-700' : ($row->type === 'cuti' ? 'bg-blue-100 text-blue-700' : 'bg-emerald-100 text-emerald-700') }}">
                                    {{ $row->type }}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap font-bold text-slate-800">
                                {{ $row->start_date->format('d/m/Y') }} - {{ $row->end_date->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3 max-w-xs">
                                <p class="text-slate-800 line-clamp-2 text-[11px]">{{ $row->reason }}</p>
                                @if($row->attachment)
                                    <a href="{{ asset('storage/' . $row->attachment) }}" target="_blank" class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-600 hover:text-emerald-700 underline mt-1">
                                        <i class="fa-solid fa-paperclip"></i> Lihat Lampiran
                                    </a>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($row->status === 'pending')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-700">
                                        ⏳ Pending
                                    </span>
                                @elseif($row->status === 'approved')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-700">
                                        ✅ Disetujui
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-rose-100 text-rose-700">
                                        ❌ Ditolak
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    @if($row->status !== 'approved')
                                        <form method="POST" action="{{ route('admin.leave.approve', $row->id) }}">
                                            @csrf
                                            <button type="submit" onclick="return confirm('Apakah Anda yakin ingin menyetujui pengajuan ini?')"
                                                class="py-1.5 px-3 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white font-extrabold text-[11px] transition shadow-sm flex items-center gap-1">
                                                <i class="fa-solid fa-check"></i> Setujui
                                            </button>
                                        </form>
                                    @endif

                                    @if($row->status !== 'rejected')
                                        <button type="button" 
                                            @click="rejectModal.show = true; rejectModal.actionUrl = '{{ route('admin.leave.reject', $row->id) }}'; rejectModal.name = '{{ $row->user->name }}'"
                                            class="py-1.5 px-3 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-100 font-extrabold text-[11px] transition border border-rose-200 flex items-center gap-1">
                                            <i class="fa-solid fa-xmark"></i> Tolak
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-slate-400">
                                <i class="fa-solid fa-inbox text-3xl mb-2"></i>
                                <div>Tidak ditemukan pengajuan izin/sakit/cuti.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $leaveRequests->links() }}
        </div>
    </div>

    <!-- Reject Modal -->
    <div x-show="rejectModal.show" x-cloak class="fixed inset-0 z-50 flex items-center justify-center px-4 bg-slate-900/80 backdrop-blur-sm">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 space-y-4 shadow-2xl border border-slate-100">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-extrabold text-slate-900 text-base flex items-center gap-2 text-rose-600">
                    <i class="fa-solid fa-circle-xmark"></i> Tolak Pengajuan Izin
                </h3>
                <button type="button" @click="rejectModal.show = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <p class="text-xs text-slate-600 font-medium">
                Anda akan menolak pengajuan izin milik peserta <strong class="text-slate-900" x-text="rejectModal.name"></strong>. Harap berikan alasan penolakan untuk dikirimkan ke peserta.
            </p>

            <form :action="rejectModal.actionUrl" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-extrabold text-slate-700 mb-1">
                        Alasan Penolakan <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="admin_note" rows="3" required
                        placeholder="Contoh: Alasan tidak melampirkan surat dokter resmi / periode tidak sesuai."
                        class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-rose-500"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2">
                    <button type="button" @click="rejectModal.show = false" class="py-2.5 px-4 rounded-xl text-xs font-bold text-slate-500 hover:bg-slate-100">
                        Batal
                    </button>
                    <button type="submit" class="py-2.5 px-4 rounded-xl text-xs font-extrabold text-white bg-rose-600 hover:bg-rose-700 shadow-md shadow-rose-600/20">
                        Konfirmasi Penolakan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
