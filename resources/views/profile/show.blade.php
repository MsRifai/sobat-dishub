@extends('layouts.app')

@section('title', 'Profil Saya - SOBAT Dishub')

@section('content')
<div class="max-w-md mx-auto space-y-6 pb-8">

    <!-- Header Card -->
    <div class="bg-gradient-to-r from-emerald-600 to-teal-700 rounded-3xl p-6 text-white shadow-md relative overflow-hidden text-center space-y-3">
        <div class="w-20 h-20 rounded-3xl bg-white/20 border-2 border-white/40 flex items-center justify-center font-extrabold text-2xl mx-auto shadow-lg backdrop-blur-sm">
            {{ strtoupper(substr($user->name, 0, 2)) }}
        </div>
        <div>
            <h3 class="font-extrabold text-xl text-white leading-tight">{{ $user->name }}</h3>
            <p class="text-xs text-emerald-100 font-semibold mt-1">{{ $user->asal_instansi_kampus ?? 'Peserta Magang' }}</p>
            <div class="inline-flex items-center gap-1.5 bg-white/10 px-3 py-1 rounded-full text-[11px] font-extrabold mt-2 border border-white/20">
                <i class="fa-solid fa-id-card"></i> {{ $user->username }} ({{ strtoupper($user->role) }})
            </div>
        </div>
    </div>

    <!-- Alert Flash Messages -->
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl text-xs font-bold flex items-center gap-2">
            <i class="fa-solid fa-circle-check text-emerald-600 text-base"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl text-xs font-bold space-y-1">
            <div class="flex items-center gap-2 text-rose-700">
                <i class="fa-solid fa-circle-exclamation text-base"></i>
                <span>Terdapat kesalahan:</span>
            </div>
            <ul class="list-disc list-inside text-[11px] font-semibold text-rose-600">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Presensi Summary Stats Card -->
    <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-200/80 space-y-3">
        <h4 class="font-extrabold text-slate-900 text-xs uppercase tracking-wider flex items-center gap-2">
            <i class="fa-solid fa-chart-simple text-emerald-500"></i> Statistik Presensi Saya
        </h4>
        <div class="grid grid-cols-3 gap-2.5 text-center">
            <div class="p-3 bg-emerald-50 border border-emerald-100 rounded-2xl">
                <div class="text-xl font-extrabold text-emerald-600">{{ $statTepatWaktu }}</div>
                <div class="text-[10px] text-emerald-700 font-bold mt-0.5">Tepat Waktu</div>
            </div>
            <div class="p-3 bg-purple-50 border border-purple-100 rounded-2xl">
                <div class="text-xl font-extrabold text-purple-600">{{ $statTerlambat }}</div>
                <div class="text-[10px] text-purple-700 font-bold mt-0.5">Terlambat</div>
            </div>
            <div class="p-3 bg-blue-50 border border-blue-100 rounded-2xl">
                <div class="text-xl font-extrabold text-blue-600">{{ $statIzin }}</div>
                <div class="text-[10px] text-blue-700 font-bold mt-0.5">Izin/Sakit</div>
            </div>
        </div>
    </div>

    <!-- Edit Profile Form Card -->
    <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-200/80 space-y-4">
        <div class="border-b border-slate-100 pb-3">
            <h4 class="font-extrabold text-slate-900 text-base flex items-center gap-2">
                <i class="fa-solid fa-user-gear text-emerald-500"></i> Edit Informasi Biodata
            </h4>
            <p class="text-[11px] text-slate-500 font-medium">Perbarui nama, asal kampus, dan email Anda</p>
        </div>

        <form action="{{ route('profile.update') }}" method="POST" class="space-y-4">
            @csrf

            <!-- Username (Read-only) -->
            <div>
                <label class="block text-xs font-extrabold text-slate-400 uppercase tracking-wider mb-1">
                    Username (ID Sistem)
                </label>
                <input type="text" value="{{ $user->username }}" disabled
                    class="w-full py-2.5 px-3 bg-slate-100 border border-slate-200 rounded-2xl text-xs font-bold text-slate-500 cursor-not-allowed">
            </div>

            <!-- Nama Lengkap -->
            <div>
                <label for="name" class="block text-xs font-extrabold text-slate-700 mb-1">
                    Nama Lengkap <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="name" name="name" required value="{{ old('name', $user->name) }}"
                    class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>

            <!-- Asal Instansi / Kampus -->
            <div>
                <label for="asal_instansi_kampus" class="block text-xs font-extrabold text-slate-700 mb-1">
                    Asal Instansi / Universitas <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="asal_instansi_kampus" name="asal_instansi_kampus" required value="{{ old('asal_instansi_kampus', $user->asal_instansi_kampus) }}"
                    class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-xs font-extrabold text-slate-700 mb-1">
                    Alamat Email <span class="text-slate-400 font-normal">(Opsional)</span>
                </label>
                <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}"
                    class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>

            <button type="submit" 
                class="w-full py-3 px-4 bg-emerald-500 hover:bg-emerald-600 text-white font-extrabold text-xs rounded-2xl shadow-md shadow-emerald-500/20 transition flex items-center justify-center gap-2">
                <i class="fa-solid fa-floppy-disk"></i>
                <span>Simpan Perubahan Profil</span>
            </button>
        </form>
    </div>

    <!-- Ubah Kata Sandi Form Card -->
    <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-200/80 space-y-4">
        <div class="border-b border-slate-100 pb-3">
            <h4 class="font-extrabold text-slate-900 text-base flex items-center gap-2">
                <i class="fa-solid fa-key text-amber-500"></i> Ubah Kata Sandi
            </h4>
            <p class="text-[11px] text-slate-500 font-medium">Perbarui kata sandi untuk keamanan akun Anda</p>
        </div>

        <form action="{{ route('profile.password') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label for="current_password" class="block text-xs font-extrabold text-slate-700 mb-1">
                    Kata Sandi Saat Ini <span class="text-rose-500">*</span>
                </label>
                <input type="password" id="current_password" name="current_password" required
                    placeholder="••••••••"
                    class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-amber-500">
            </div>

            <div>
                <label for="password" class="block text-xs font-extrabold text-slate-700 mb-1">
                    Kata Sandi Baru <span class="text-rose-500">*</span>
                </label>
                <input type="password" id="password" name="password" required
                    placeholder="Minimal 6 karakter"
                    class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-amber-500">
            </div>

            <div>
                <label for="password_confirmation" class="block text-xs font-extrabold text-slate-700 mb-1">
                    Konfirmasi Kata Sandi Baru <span class="text-rose-500">*</span>
                </label>
                <input type="password" id="password_confirmation" name="password_confirmation" required
                    placeholder="Ulangi kata sandi baru"
                    class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-amber-500">
            </div>

            <button type="submit" 
                class="w-full py-3 px-4 bg-amber-500 hover:bg-amber-600 text-white font-extrabold text-xs rounded-2xl shadow-md shadow-amber-500/20 transition flex items-center justify-center gap-2">
                <i class="fa-solid fa-lock"></i>
                <span>Ganti Kata Sandi</span>
            </button>
        </form>
    </div>

    <!-- Quick Logout Section -->
    <div class="p-4 bg-white rounded-3xl border border-slate-200/80 text-center">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full py-3 px-4 bg-rose-50 hover:bg-rose-100 text-rose-600 font-extrabold text-xs rounded-2xl border border-rose-200 transition flex items-center justify-center gap-2">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Keluar dari Akun (Logout)</span>
            </button>
        </form>
    </div>

</div>
@endsection
