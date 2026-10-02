@extends('layouts.app')

@section('title', 'Pengajuan Izin / Sakit / Cuti - SOBAT Dishub')

@section('content')
<div class="max-w-md mx-auto space-y-6 pb-8">

    <!-- Header Banner -->
    <div class="bg-gradient-to-r from-emerald-600 to-teal-700 rounded-3xl p-6 text-white shadow-md relative overflow-hidden">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-2xl bg-white/20 flex items-center justify-center text-white text-2xl font-bold backdrop-blur-sm">
                <i class="fa-solid fa-file-signature"></i>
            </div>
            <div>
                <h3 class="font-extrabold text-lg text-white leading-tight">Pengajuan Izin & Cuti</h3>
                <p class="text-xs text-emerald-100 font-medium">Modul permohonan ketidakhadiran peserta magang</p>
            </div>
        </div>
    </div>

    <!-- Alert Success / Error Messages -->
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl text-xs font-semibold flex items-center gap-2">
            <i class="fa-solid fa-circle-check text-emerald-600 text-base"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl text-xs font-semibold flex items-center gap-2">
            <i class="fa-solid fa-circle-exclamation text-rose-600 text-base"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Form Section Collapsible / Card -->
    <div x-data="{ showForm: false }" class="bg-white rounded-3xl p-5 shadow-sm border border-slate-200/80 space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h4 class="font-extrabold text-slate-900 text-base">Formulir Pengajuan Baru</h4>
                <p class="text-[11px] text-slate-500 font-medium">Isi detail alasan dan unggah bukti pendukung</p>
            </div>
            <button type="button" @click="showForm = !showForm" 
                class="py-2 px-3 rounded-xl bg-emerald-50 text-emerald-600 hover:bg-emerald-100 font-extrabold text-xs transition flex items-center gap-1.5">
                <i class="fa-solid" :class="showForm ? 'fa-minus' : 'fa-plus'"></i>
                <span x-text="showForm ? 'Tutup Form' : 'Buat Pengajuan'"></span>
            </button>
        </div>

        <form x-show="showForm" x-cloak action="{{ route('presensi.leave.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4 pt-3 border-t border-slate-100">
            @csrf

            <!-- Jenis Pengajuan -->
            <div>
                <label class="block text-xs font-extrabold text-slate-700 mb-1.5 uppercase tracking-wider">
                    Jenis Pengajuan <span class="text-rose-500">*</span>
                </label>
                <div class="grid grid-cols-3 gap-2">
                    <label class="cursor-pointer">
                        <input type="radio" name="type" value="izin" checked class="peer sr-only">
                        <div class="py-2.5 px-3 text-center rounded-2xl border-2 border-slate-200 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:text-emerald-700 font-extrabold text-xs text-slate-600 transition">
                            <i class="fa-solid fa-envelope-open-text block text-base mb-1 text-emerald-600"></i> Izin
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="type" value="sakit" class="peer sr-only">
                        <div class="py-2.5 px-3 text-center rounded-2xl border-2 border-slate-200 peer-checked:border-amber-500 peer-checked:bg-amber-50 peer-checked:text-amber-700 font-extrabold text-xs text-slate-600 transition">
                            <i class="fa-solid fa-notes-medical block text-base mb-1 text-amber-600"></i> Sakit
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="type" value="cuti" class="peer sr-only">
                        <div class="py-2.5 px-3 text-center rounded-2xl border-2 border-slate-200 peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:text-blue-700 font-extrabold text-xs text-slate-600 transition">
                            <i class="fa-solid fa-business-time block text-base mb-1 text-blue-600"></i> Cuti
                        </div>
                    </label>
                </div>
            </div>

            <!-- Tanggal Mulai & Tanggal Selesai -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="start_date" class="block text-xs font-extrabold text-slate-700 mb-1">
                        Dari Tanggal <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" id="start_date" name="start_date" required
                        value="{{ old('start_date', date('Y-m-d')) }}"
                        class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label for="end_date" class="block text-xs font-extrabold text-slate-700 mb-1">
                        Sampai Tanggal <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" id="end_date" name="end_date" required
                        value="{{ old('end_date', date('Y-m-d')) }}"
                        class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
            </div>

            <!-- Alasan Pengajuan -->
            <div>
                <label for="reason" class="block text-xs font-extrabold text-slate-700 mb-1">
                    Alasan / Keterangan Lengkap <span class="text-rose-500">*</span>
                </label>
                <textarea id="reason" name="reason" rows="3" required
                    placeholder="Jelaskan alasan pengajuan izin/sakit/cuti secara lengkap..."
                    class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
            </div>

            <!-- Lampiran / Proof Upload -->
            <div>
                <label for="attachment" class="block text-xs font-extrabold text-slate-700 mb-1">
                    Unggah Berkas Lampiran <span class="text-slate-400 font-normal">(Surat Dokter/Lampiran PDF/JPG)</span>
                </label>
                <input type="file" id="attachment" name="attachment" accept=".jpg,.jpeg,.png,.pdf"
                    class="w-full py-2 px-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs text-slate-600 file:mr-3 file:py-1 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-emerald-500 file:text-white hover:file:bg-emerald-600">
                <p class="text-[10px] text-slate-400 mt-1 font-medium">Format: JPG, PNG, PDF (Maksimal 5MB)</p>
            </div>

            <!-- Submit Button -->
            <button type="submit" 
                class="w-full py-3 px-4 bg-emerald-500 hover:bg-emerald-600 text-white font-extrabold text-xs rounded-2xl shadow-md shadow-emerald-500/20 transition flex items-center justify-center gap-2">
                <i class="fa-solid fa-paper-plane"></i>
                <span>Kirim Pengajuan</span>
            </button>
        </form>
    </div>

    <!-- History List -->
    <div class="space-y-3">
        <h4 class="font-extrabold text-slate-900 text-sm flex items-center gap-2 px-1">
            <i class="fa-solid fa-clock-rotate-left text-emerald-500"></i> Riwayat Pengajuan Saya
        </h4>

        @forelse($leaveRequests as $item)
            <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-200/80 space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2.5">
                        <span class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-sm
                            {{ $item->type === 'sakit' ? 'bg-amber-100 text-amber-700' : ($item->type === 'cuti' ? 'bg-blue-100 text-blue-700' : 'bg-emerald-100 text-emerald-700') }}">
                            <i class="{{ $item->type === 'sakit' ? 'fa-solid fa-notes-medical' : ($item->type === 'cuti' ? 'fa-solid fa-business-time' : 'fa-solid fa-envelope-open-text') }}"></i>
                        </span>
                        <div>
                            <span class="font-extrabold text-slate-900 text-sm uppercase">{{ $item->type }}</span>
                            <div class="text-[10px] text-slate-400 font-semibold">Diajukan: {{ $item->created_at->format('d M Y H:i') }}</div>
                        </div>
                    </div>

                    <!-- Status Badge -->
                    @if($item->status === 'pending')
                        <span class="px-3 py-1 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-700 flex items-center gap-1">
                            <i class="fa-solid fa-clock animate-spin"></i> Pending
                        </span>
                    @elseif($item->status === 'approved')
                        <span class="px-3 py-1 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-700 flex items-center gap-1">
                            <i class="fa-solid fa-circle-check"></i> Disetujui
                        </span>
                    @else
                        <span class="px-3 py-1 rounded-full text-[10px] font-extrabold bg-rose-100 text-rose-700 flex items-center gap-1">
                            <i class="fa-solid fa-circle-xmark"></i> Ditolak
                        </span>
                    @endif
                </div>

                <!-- Detail Info -->
                <div class="text-xs space-y-1.5 font-medium text-slate-700">
                    <div class="flex items-center gap-2">
                        <i class="fa-regular fa-calendar text-emerald-500"></i>
                        <span>Periode: <strong class="text-slate-900 font-extrabold">{{ $item->start_date->format('d M Y') }}</strong> s/d <strong class="text-slate-900 font-extrabold">{{ $item->end_date->format('d M Y') }}</strong></span>
                    </div>

                    <div>
                        <span class="text-slate-400 text-[11px] block">Alasan:</span>
                        <p class="text-slate-900 font-semibold text-xs leading-relaxed mt-0.5 bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                            {{ $item->reason }}
                        </p>
                    </div>

                    @if($item->attachment)
                        <div class="pt-1">
                            <a href="{{ asset('storage/' . $item->attachment) }}" target="_blank" 
                                class="inline-flex items-center gap-1.5 text-xs text-emerald-600 hover:text-emerald-700 font-extrabold underline">
                                <i class="fa-solid fa-paperclip"></i> Lihat Berkas Lampiran
                            </a>
                        </div>
                    @endif

                    @if($item->status === 'rejected' && $item->admin_note)
                        <div class="mt-2 p-3 bg-rose-50 border border-rose-200 rounded-2xl text-rose-700 text-xs space-y-1">
                            <div class="font-bold flex items-center gap-1 text-rose-800">
                                <i class="fa-solid fa-comment-dots"></i> Catatan Rejection Admin:
                            </div>
                            <p class="text-[11px] font-medium">{{ $item->admin_note }}</p>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-white rounded-3xl p-8 text-center border border-slate-200/80 space-y-2">
                <i class="fa-solid fa-folder-open text-4xl text-slate-300"></i>
                <h5 class="font-extrabold text-slate-700 text-sm">Belum Ada Pengajuan</h5>
                <p class="text-xs text-slate-400">Anda belum pernah membuat pengajuan izin, sakit, atau cuti.</p>
            </div>
        @endforelse

        <div class="mt-4">
            {{ $leaveRequests->links() }}
        </div>
    </div>

</div>
@endsection
