<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Office Location default (Kantor Induk Dishub DIY)
        \App\Models\OfficeLocation::create([
            'location_name' => 'Kantor Induk Dishub DIY',
            'latitude' => -7.79560000,
            'longitude' => 110.36950000,
            'radius_meters' => 100, // 100 meters default radius
            'is_active' => true,
        ]);

        // Default Admin User
        User::create([
            'name' => 'Admin Dishub',
            'username' => 'admin',
            'email' => 'admin@dishub.go.id',
            'role' => 'admin',
            'asal_instansi_kampus' => 'Dinas Perhubungan',
            'is_active' => true,
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
        ]);

        // Default Magang User 1
        User::create([
            'name' => 'Budi Santoso',
            'username' => 'magang01',
            'email' => 'budi@student.ac.id',
            'role' => 'magang',
            'asal_instansi_kampus' => 'Universitas Gadjah Mada',
            'is_active' => true,
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
        ]);

        // Default Magang User 2
        $user2 = User::create([
            'name' => 'Siti Rahma',
            'username' => 'magang02',
            'email' => 'siti@student.ac.id',
            'role' => 'magang',
            'asal_instansi_kampus' => 'Universitas Negeri Yogyakarta',
            'is_active' => true,
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
        ]);

        $magang1 = User::where('username', 'magang01')->first();

        // Sample Attendance Hari Ini (Budi - Tepat Waktu)
        \App\Models\Attendance::create([
            'user_id' => $magang1->id,
            'date' => \Carbon\Carbon::now('Asia/Jakarta')->toDateString(),
            'check_in_time' => '07:15:00',
            'check_in_lat' => -7.79561000,
            'check_in_long' => 110.36952000,
            'distance_in_meters' => 12,
            'status' => 'tepat_waktu',
            'late_minutes' => 0,
        ]);

        // Sample Attendance Hari Ini (Siti - Terlambat)
        \App\Models\Attendance::create([
            'user_id' => $user2->id,
            'date' => \Carbon\Carbon::now('Asia/Jakarta')->toDateString(),
            'check_in_time' => '07:45:00',
            'check_in_lat' => -7.79563000,
            'check_in_long' => 110.36954000,
            'distance_in_meters' => 25,
            'status' => 'terlambat',
            'late_minutes' => 15,
        ]);

        // Default Magang User 3 (Belum Absen Hari Ini - Siap Diuji Check-in)
        User::create([
            'name' => 'Ahmad Fauzi',
            'username' => 'magang03',
            'email' => 'ahmad@student.ac.id',
            'role' => 'magang',
            'asal_instansi_kampus' => 'Universitas Amikom Yogyakarta',
            'is_active' => true,
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
        ]);
    }
}
