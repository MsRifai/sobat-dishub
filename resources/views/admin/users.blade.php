@extends('layouts.app')

@section('title', 'Kelola User - SOBAT Dishub')

@section('content')
<div x-data="{ 
    createModal: false, 
    editModal: { show: false, actionUrl: '', name: '', username: '', email: '', asal: '', role: '' },
    resetModal: { show: false, actionUrl: '', name: '', username: '' }
}" class="space-y-6">
    
    <!-- Title & Add Button -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Kelola Pengguna</h1>
            <p class="text-xs text-slate-500 mt-1">Daftar akun peserta magang dan administrator sistem SOBAT Dishub</p>
        </div>

        <button type="button" @click="createModal = true"
            class="py-2.5 px-4 bg-emerald-500 hover:bg-emerald-600 text-white font-extrabold text-xs rounded-2xl shadow-lg shadow-emerald-500/20 transition flex items-center gap-2">
            <i class="fa-solid fa-user-plus"></i> Tambah Pengguna Baru
        </button>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-semibold flex items-center gap-2">
            <i class="fa-solid fa-circle-check text-base text-emerald-500"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold flex items-center gap-2">
            <i class="fa-solid fa-triangle-exclamation text-base text-rose-500"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Users Table Card -->
    <div class="bg-white rounded-3xl overflow-hidden shadow-sm border border-slate-200/80">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 text-[11px] font-extrabold uppercase tracking-wider text-slate-400 border-b border-slate-100">
                        <th class="py-4 px-6">Nama Lengkap</th>
                        <th class="py-4 px-6">Username</th>
                        <th class="py-4 px-6">Instansi / Kampus</th>
                        <th class="py-4 px-6 text-center">Peran (Role)</th>
                        <th class="py-4 px-6 text-center">Status Akun</th>
                        <th class="py-4 px-6 text-right">Aksi & Manajemen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                    @foreach($users as $user)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-4 px-6 font-bold text-slate-900">
                            {{ $user->name }}
                            @if($user->email)
                                <div class="text-[10px] text-slate-400 font-normal">{{ $user->email }}</div>
                            @endif
                        </td>
                        <td class="py-4 px-6 font-mono font-bold text-emerald-600">{{ $user->username }}</td>
                        <td class="py-4 px-6 text-slate-600 font-medium">{{ $user->asal_instansi_kampus ?? '-' }}</td>
                        <td class="py-4 px-6 text-center">
                            @if($user->role === 'admin')
                                <span class="px-3 py-1 rounded-full text-[10px] font-extrabold bg-blue-100 text-blue-700">
                                    Admin
                                </span>
                            @else
                                <span class="px-3 py-1 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-700">
                                    Magang
                                </span>
                            @endif
                        </td>
                        <td class="py-4 px-6 text-center">
                            @if($user->is_active)
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700">
                                    Aktif
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700">
                                    Non-Aktif
                                </span>
                            @endif
                        </td>
                        <td class="py-4 px-6 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-1.5">
                                <!-- Rapor Presensi (Khusus Magang) -->
                                @if($user->isMagang())
                                    <a href="{{ route('admin.users.report', $user->id) }}" 
                                        class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center gap-1"
                                        title="Lihat Rapor Presensi">
                                        <i class="fa-solid fa-chart-user text-emerald-600"></i> Rapor
                                    </a>
                                @endif

                                <!-- Edit Button -->
                                <button type="button" 
                                    @click="
                                        editModal.show = true; 
                                        editModal.actionUrl = '{{ route('admin.users.update', $user->id) }}';
                                        editModal.name = '{{ addslashes($user->name) }}';
                                        editModal.username = '{{ addslashes($user->username) }}';
                                        editModal.email = '{{ addslashes($user->email ?? '') }}';
                                        editModal.asal = '{{ addslashes($user->asal_instansi_kampus ?? '') }}';
                                        editModal.role = '{{ $user->role }}';
                                    "
                                    class="p-2 rounded-xl bg-blue-50 text-blue-600 hover:bg-blue-100 transition" title="Edit Biodata">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>

                                <!-- Reset Password Button -->
                                <button type="button" 
                                    @click="
                                        resetModal.show = true;
                                        resetModal.actionUrl = '{{ route('admin.users.reset-password', $user->id) }}';
                                        resetModal.name = '{{ addslashes($user->name) }}';
                                        resetModal.username = '{{ addslashes($user->username) }}';
                                    "
                                    class="p-2 rounded-xl bg-amber-50 text-amber-600 hover:bg-amber-100 transition" title="Reset Password">
                                    <i class="fa-solid fa-key"></i>
                                </button>

                                <!-- Toggle Active -->
                                <form method="POST" action="{{ route('admin.users.toggle', $user->id) }}" class="inline">
                                    @csrf
                                    <button type="submit" 
                                        class="py-1.5 px-3 rounded-xl text-[10px] font-bold transition {{ $user->is_active ? 'bg-rose-100 text-rose-700 hover:bg-rose-200' : 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200' }}">
                                        {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
        <div class="p-4 border-t border-slate-100 bg-slate-50/50">
            {{ $users->links() }}
        </div>
        @endif
    </div>

    <!-- Create User Modal -->
    <div x-show="createModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center px-4 bg-slate-900/80 backdrop-blur-sm">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 space-y-4 shadow-2xl relative border border-slate-100">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-extrabold text-slate-900 text-base">Tambah Pengguna Baru</h3>
                <button type="button" @click="createModal = false" class="text-slate-400 hover:text-slate-900">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4">
                @csrf
                
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Lengkap</label>
                    <input type="text" name="name" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Username</label>
                    <input type="text" name="username" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Asal Instansi / Kampus</label>
                    <input type="text" name="asal_instansi_kampus" placeholder="Misal: Universitas Gadjah Mada" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Peran (Role)</label>
                        <select name="role" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="magang">Magang</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Password</label>
                        <input type="password" name="password" required minlength="6" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="createModal = false" class="py-2.5 px-4 bg-slate-100 text-slate-700 text-xs font-bold rounded-2xl hover:bg-slate-200">Batal</button>
                    <button type="submit" class="py-2.5 px-4 bg-emerald-500 text-white text-xs font-extrabold rounded-2xl hover:bg-emerald-600 shadow">Simpan Akun</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div x-show="editModal.show" x-cloak class="fixed inset-0 z-50 flex items-center justify-center px-4 bg-slate-900/80 backdrop-blur-sm">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 space-y-4 shadow-2xl relative border border-slate-100">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-extrabold text-slate-900 text-base flex items-center gap-2">
                    <i class="fa-solid fa-user-pen text-blue-500"></i> Edit Data Pengguna
                </h3>
                <button type="button" @click="editModal.show = false" class="text-slate-400 hover:text-slate-900">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form :action="editModal.actionUrl" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Lengkap</label>
                    <input type="text" name="name" x-model="editModal.name" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Username</label>
                    <input type="text" name="username" x-model="editModal.username" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Asal Instansi / Kampus</label>
                    <input type="text" name="asal_instansi_kampus" x-model="editModal.asal" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Email</label>
                    <input type="email" name="email" x-model="editModal.email" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Peran (Role)</label>
                    <select name="role" x-model="editModal.role" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="magang">Magang</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="editModal.show = false" class="py-2.5 px-4 bg-slate-100 text-slate-700 text-xs font-bold rounded-2xl hover:bg-slate-200">Batal</button>
                    <button type="submit" class="py-2.5 px-4 bg-blue-600 text-white text-xs font-extrabold rounded-2xl hover:bg-blue-700 shadow">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reset Password Modal -->
    <div x-show="resetModal.show" x-cloak class="fixed inset-0 z-50 flex items-center justify-center px-4 bg-slate-900/80 backdrop-blur-sm">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 space-y-4 shadow-2xl relative border border-slate-100">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-extrabold text-slate-900 text-base flex items-center gap-2 text-amber-600">
                    <i class="fa-solid fa-key"></i> Reset Password Pengguna
                </h3>
                <button type="button" @click="resetModal.show = false" class="text-slate-400 hover:text-slate-900">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <p class="text-xs text-slate-600 font-medium">
                Reset password untuk pengguna <strong class="text-slate-900" x-text="resetModal.name"></strong> (<span x-text="resetModal.username"></span>). Masukkan password baru di bawah ini.
            </p>

            <form :action="resetModal.actionUrl" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Password Baru</label>
                    <input type="password" name="password" required minlength="6" placeholder="Masukkan password baru..."
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-amber-500">
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="resetModal.show = false" class="py-2.5 px-4 bg-slate-100 text-slate-700 text-xs font-bold rounded-2xl hover:bg-slate-200">Batal</button>
                    <button type="submit" class="py-2.5 px-4 bg-amber-500 text-white text-xs font-extrabold rounded-2xl hover:bg-amber-600 shadow">Simpan Password Baru</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
