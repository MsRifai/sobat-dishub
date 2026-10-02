<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Tampilkan Halaman Profil User
     */
    public function show()
    {
        $user = Auth::user();

        // Stat Ringkasan Presensi User Selama Magang
        $statTepatWaktu = Attendance::where('user_id', $user->id)
            ->where('status', 'tepat_waktu')
            ->count();

        $statTerlambat = Attendance::where('user_id', $user->id)
            ->where('status', 'terlambat')
            ->count();

        $statIzin = Attendance::where('user_id', $user->id)
            ->whereIn('status', ['sakit', 'izin', 'cuti'])
            ->count();

        $totalAttendance = Attendance::where('user_id', $user->id)->count();

        return view('profile.show', compact(
            'user',
            'statTepatWaktu',
            'statTerlambat',
            'statIzin',
            'totalAttendance'
        ));
    }

    /**
     * Update Informasi Biodata Profil
     */
    public function updateProfile(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', Rule::unique('users')->ignore($user->id)],
            'asal_instansi_kampus' => ['required', 'string', 'max:255'],
        ], [
            'email.unique' => 'Alamat email ini sudah digunakan oleh pengguna lain.',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'asal_instansi_kampus' => $request->asal_instansi_kampus,
        ]);

        return back()->with('success', 'Informasi profil berhasil diperbarui.');
    }

    /**
     * Update Kata Sandi / Password
     */
    public function updatePassword(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
            'password.min' => 'Kata sandi baru minimal 6 karakter.',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Kata sandi saat ini tidak cocok.']);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Kata sandi berhasil diubah.');
    }
}
