<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Tampilkan Form Login
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return Auth::user()->isAdmin() 
                ? redirect()->route('admin.dashboard') 
                : redirect()->route('presensi.index');
        }
        return view('auth.login');
    }

    /**
     * Proses Login User
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt(['username' => $credentials['username'], 'password' => $credentials['password'], 'is_active' => true], $request->boolean('remember'))) {
            $request->session()->regenerate();

            /** @var \App\Models\User $user */
            $user = Auth::user();

            if ($user->isAdmin()) {
                return redirect()->intended(route('admin.dashboard'));
            }

            return redirect()->intended(route('presensi.index'));
        }

        return back()->withErrors([
            'username' => 'Username atau password salah, atau akun Anda tidak aktif.',
        ])->onlyInput('username');
    }

    /**
     * Tampilkan Form Pendaftaran Akun Magang Baru
     */
    public function showRegister()
    {
        if (Auth::check()) {
            return Auth::user()->isAdmin() 
                ? redirect()->route('admin.dashboard') 
                : redirect()->route('presensi.index');
        }
        return view('auth.register');
    }

    /**
     * Proses Registrasi Akun Magang Baru
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:100', 'unique:users,username'],
            'email' => ['nullable', 'email', 'unique:users,email'],
            'asal_instansi_kampus' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'username.unique' => 'Username ini sudah terdaftar dalam sistem.',
            'email.unique' => 'Alamat email ini sudah digunakan.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'password.min' => 'Kata sandi minimal 6 karakter.',
        ]);

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'asal_instansi_kampus' => $request->asal_instansi_kampus,
            'role' => 'magang',
            'is_active' => true,
            'password' => Hash::make($request->password),
        ]);

        Auth::login($user);

        return redirect()->route('presensi.index')->with('success', 'Pendaftaran akun magang berhasil! Selamat datang di SOBAT Dishub.');
    }

    /**
     * Tampilkan Form Lupa Kata Sandi
     */
    public function showForgotPassword()
    {
        if (Auth::check()) {
            return Auth::user()->isAdmin() 
                ? redirect()->route('admin.dashboard') 
                : redirect()->route('presensi.index');
        }
        return view('auth.forgot_password');
    }

    /**
     * Proses Reset Kata Sandi Mandiri (Verifikasi Username + Instansi)
     */
    public function resetPasswordSelf(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string'],
            'asal_instansi_kampus' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'username.required' => 'Username wajib diisi.',
            'asal_instansi_kampus.required' => 'Asal instansi / kampus wajib diisi untuk verifikasi.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
            'password.min' => 'Kata sandi baru minimal 6 karakter.',
        ]);

        $user = User::where('username', $request->username)->first();

        if (!$user) {
            return back()->withErrors(['username' => 'Username tidak ditemukan dalam sistem.'])->withInput();
        }

        // Verifikasi fleksibel Asal Instansi / Kampus terdaftar (case-insensitive substring match)
        $inputCampus = strtolower(trim($request->asal_instansi_kampus));
        $registeredCampus = strtolower(trim($user->asal_instansi_kampus ?? ''));

        $isCampusValid = $registeredCampus !== '' && (
            str_contains($registeredCampus, $inputCampus) || 
            str_contains($inputCampus, $registeredCampus)
        );

        if (!$isCampusValid) {
            return back()->withErrors([
                'asal_instansi_kampus' => 'Verifikasi gagal. Asal instansi / kampus tidak cocok dengan data terdaftar akun Anda.'
            ])->withInput();
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('login')->with('success', "Kata sandi untuk akun {$user->name} ({$user->username}) berhasil direset. Silakan masuk menggunakan kata sandi baru.");
    }

    /**
     * Logout Session
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
