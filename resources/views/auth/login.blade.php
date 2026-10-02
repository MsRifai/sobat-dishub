@extends('layouts.app')

@section('title', 'Login Presensi - SOBAT Dishub')

@section('content')
<div class="min-h-screen bg-slate-50 flex flex-col justify-center items-center py-8 px-4 text-slate-800">
    <div class="w-full max-w-md">
        
        <!-- Header Brand Card -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 mb-3 drop-shadow-md">
                <img src="{{ asset('images/logo-dishub.png') }}" alt="Logo Dishub" class="w-full h-full object-contain">
            </div>
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">
                SOBAT <span class="text-emerald-600">Dishub</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1 font-medium">Sistem Olah & Presensi Magang Terpadu - Dinas Perhubungan</p>
        </div>

        <!-- Login Form White Card -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200/80 relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-emerald-500 via-teal-400 to-blue-500"></div>

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <!-- Username -->
                <div>
                    <label for="username" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Username Peserta
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-user"></i>
                        </div>
                        <input type="text" name="username" id="username" value="{{ old('username') }}" required autofocus
                            placeholder="Masukkan username"
                            class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-semibold text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                    </div>
                    @error('username')
                        <p class="text-xs text-rose-500 mt-1.5 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Kata Sandi
                    </label>
                    <div class="relative" x-data="{ showPass: false }">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-lock"></i>
                        </div>
                        <input :type="showPass ? 'text' : 'password'" name="password" id="password" required
                            placeholder="••••••••"
                            class="w-full pl-10 pr-10 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-semibold text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                        <button type="button" @click="showPass = !showPass" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 transition">
                            <i class="fa-solid" :class="showPass ? 'fa-eye-slash' : 'fa-eye'"></i>
                        </button>
                    </div>
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="flex items-center justify-between text-xs">
                    <label class="flex items-center gap-2 cursor-pointer text-slate-600 hover:text-slate-900 font-medium">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded bg-slate-100 border-slate-300 text-emerald-600 focus:ring-emerald-500">
                        Ingat Saya
                    </label>

                    <a href="{{ route('forgot-password') }}" class="text-emerald-600 hover:text-emerald-700 font-extrabold hover:underline transition">
                        Lupa Kata Sandi?
                    </a>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full py-3.5 px-4 bg-emerald-500 hover:bg-emerald-600 text-white font-extrabold text-sm rounded-2xl shadow-lg shadow-emerald-500/25 focus:outline-none transition transform active:scale-[0.99] flex items-center justify-center gap-2">
                    <i class="fa-solid fa-right-to-bracket"></i> Masuk Sistem Presensi
                </button>
            </form>

            <!-- Register Redirect Card -->
            <div class="mt-6 pt-5 border-t border-slate-100 text-xs text-center">
                <div class="bg-emerald-50 p-3.5 rounded-2xl border border-emerald-200">
                    <span class="text-slate-600 font-medium">Belum memiliki akun magang?</span>
                    <a href="{{ route('register') }}" class="text-emerald-700 hover:text-emerald-800 font-extrabold ml-1 underline block sm:inline mt-1 sm:mt-0">
                        Daftar Akun Baru &rarr;
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
