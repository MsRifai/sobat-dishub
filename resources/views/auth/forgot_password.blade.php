@extends('layouts.app')

@section('title', 'Lupa Kata Sandi - SOBAT Dishub')

@section('content')
<div class="min-h-screen bg-slate-50 flex flex-col justify-center items-center py-8 px-4 text-slate-800">
    <div class="w-full max-w-md space-y-6">
        
        <!-- Header Brand Card -->
        <div class="text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 mb-2 drop-shadow-md">
                <img src="{{ asset('images/logo-dishub.png') }}" alt="Logo Dishub" class="w-full h-full object-contain">
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">
                Reset <span class="text-emerald-600">Kata Sandi Mandiri</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1 font-semibold">Lakukan verifikasi username dan asal kampus untuk menyetel kata sandi baru</p>
        </div>

        <!-- Forgot Password Form White Card -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200/80 relative overflow-hidden space-y-5">
            <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-emerald-500 via-amber-400 to-teal-500"></div>

            <div class="p-3 bg-amber-50 border border-amber-200 rounded-2xl text-amber-900 text-xs flex items-start gap-2 font-medium">
                <i class="fa-solid fa-shield-halved text-amber-500 text-base shrink-0 mt-0.5"></i>
                <span>Verifikasi Identitas: Masukkan Username dan Asal Instansi/Kampus persis sesuai data terdaftar Anda.</span>
            </div>

            <form method="POST" action="{{ route('forgot-password.store') }}" class="space-y-4">
                @csrf

                <!-- Username -->
                <div>
                    <label for="username" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Username Peserta <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="username" id="username" value="{{ old('username') }}" required autofocus
                        placeholder="Contoh: ahmad_fauzi"
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-bold text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    @error('username')
                        <p class="text-[11px] text-rose-500 mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Asal Instansi / Kampus -->
                <div>
                    <label for="asal_instansi_kampus" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Asal Instansi / Kampus Terdaftar <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="asal_instansi_kampus" id="asal_instansi_kampus" value="{{ old('asal_instansi_kampus') }}" required
                        placeholder="Contoh: Universitas Gadjah Mada"
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-bold text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    @error('asal_instansi_kampus')
                        <p class="text-[11px] text-rose-500 mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password Baru & Confirm -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" x-data="{ showPass: false, showConfirm: false }">
                    <div>
                        <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Password Baru <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <input :type="showPass ? 'text' : 'password'" name="password" id="password" required
                                placeholder="••••••••"
                                class="w-full pl-3 pr-9 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <button type="button" @click="showPass = !showPass" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 transition">
                                <i class="fa-solid" :class="showPass ? 'fa-eye-slash' : 'fa-eye'"></i>
                            </button>
                        </div>
                        @error('password')
                            <p class="text-[10px] text-rose-500 mt-1 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Konfirmasi <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <input :type="showConfirm ? 'text' : 'password'" name="password_confirmation" id="password_confirmation" required
                                placeholder="••••••••"
                                class="w-full pl-3 pr-9 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <button type="button" @click="showConfirm = !showConfirm" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 transition">
                                <i class="fa-solid" :class="showConfirm ? 'fa-eye-slash' : 'fa-eye'"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full py-3.5 px-4 bg-emerald-500 hover:bg-emerald-600 text-white font-extrabold text-xs rounded-2xl shadow-lg shadow-emerald-500/25 focus:outline-none transition transform active:scale-[0.99] flex items-center justify-center gap-2">
                    <i class="fa-solid fa-key"></i> Simpan Kata Sandi Baru
                </button>
            </form>

            <div class="pt-4 border-t border-slate-100 text-center text-xs">
                <span class="text-slate-500 font-semibold">Ingat kata sandi Anda?</span>
                <a href="{{ route('login') }}" class="text-emerald-600 hover:text-emerald-700 font-extrabold ml-1 underline">
                    Kembali ke Login &rarr;
                </a>
            </div>

        </div>
    </div>
</div>
@endsection
