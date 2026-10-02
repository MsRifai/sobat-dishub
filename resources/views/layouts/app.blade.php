<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SOBAT Dishub') - Sistem Presensi Magang Terpadu</title>
    
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        sobat: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            500: '#10b981',
                            600: '#059669',
                            dark: '#0f172a',
                            accent: '#3b82f6',
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Leaflet.js -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <!-- Chart.js for Admin Analytics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        .sidebar-item-active {
            background-color: #ecfdf5;
            color: #059669;
            font-weight: 700;
            border-right: 4px solid #10b981;
        }
    </style>
    @stack('styles')
</head>
<body class="h-full text-slate-800 antialiased flex flex-col min-h-screen">

    @auth
        @if(auth()->user()->isAdmin())
            <!-- ADMIN LAYOUT: WITH SIDEBAR -->
            <div class="flex h-screen overflow-hidden bg-slate-50">
                
                <!-- Left Sidebar -->
                <aside class="w-64 bg-white border-r border-slate-200/80 flex flex-col justify-between shrink-0 z-30 shadow-sm">
                    <div>
                        <!-- Brand Logo Dishub -->
                        <div class="h-20 flex items-center gap-3 px-6 border-b border-slate-100">
                            <img src="{{ asset('images/logo-dishub.png') }}" alt="Logo Dishub" class="w-10 h-10 object-contain drop-shadow">
                            <div>
                                <span class="font-extrabold text-xl text-slate-900 tracking-tight block leading-none">
                                    SOBAT <span class="text-emerald-500">Dishub</span>
                                </span>
                                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block mt-1">Presensi Magang</span>
                            </div>
                        </div>

                        <!-- Sidebar Menu Links -->
                        <nav class="mt-6 space-y-1 px-3">
                            <a href="{{ route('admin.dashboard') }}" 
                               class="flex items-center gap-3 px-4 py-3.5 rounded-xl text-sm transition {{ request()->routeIs('admin.dashboard') ? 'sidebar-item-active' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-50' }}">
                                <i class="fa-solid fa-chart-pie text-lg {{ request()->routeIs('admin.dashboard') ? 'text-emerald-600' : 'text-slate-400' }}"></i>
                                <span>Dashboard</span>
                            </a>

                            <a href="{{ route('admin.attendances') }}" 
                               class="flex items-center gap-3 px-4 py-3.5 rounded-xl text-sm transition {{ request()->routeIs('admin.attendances') ? 'sidebar-item-active' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-50' }}">
                                <i class="fa-solid fa-clipboard-user text-lg {{ request()->routeIs('admin.attendances') ? 'text-emerald-600' : 'text-slate-400' }}"></i>
                                <span>Data Presensi</span>
                            </a>

                            <a href="{{ route('admin.leave.index') }}" 
                               class="flex items-center justify-between px-4 py-3.5 rounded-xl text-sm transition {{ request()->routeIs('admin.leave.*') ? 'sidebar-item-active' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-50' }}">
                                <div class="flex items-center gap-3">
                                    <i class="fa-solid fa-file-signature text-lg {{ request()->routeIs('admin.leave.*') ? 'text-emerald-600' : 'text-slate-400' }}"></i>
                                    <span>Pengajuan Izin</span>
                                </div>
                                @php
                                    $pendingCount = \App\Models\LeaveRequest::where('status', 'pending')->count();
                                @endphp
                                @if($pendingCount > 0)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-500 text-white shadow-sm">
                                        {{ $pendingCount }}
                                    </span>
                                @endif
                            </a>

                            <a href="{{ route('admin.office') }}" 
                               class="flex items-center gap-3 px-4 py-3.5 rounded-xl text-sm transition {{ request()->routeIs('admin.office') ? 'sidebar-item-active' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-50' }}">
                                <i class="fa-solid fa-map-location-dot text-lg {{ request()->routeIs('admin.office') ? 'text-emerald-600' : 'text-slate-400' }}"></i>
                                <span>Lokasi & Jam Kerja</span>
                            </a>

                            <a href="{{ route('admin.users') }}" 
                               class="flex items-center gap-3 px-4 py-3.5 rounded-xl text-sm transition {{ request()->routeIs('admin.users') ? 'sidebar-item-active' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-50' }}">
                                <i class="fa-solid fa-users text-lg {{ request()->routeIs('admin.users') ? 'text-emerald-600' : 'text-slate-400' }}"></i>
                                <span>Kelola User</span>
                            </a>
                        </nav>
                    </div>

                    <!-- Logout / Bottom Section -->
                    <div class="p-4 border-t border-slate-100">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold text-rose-500 hover:bg-rose-50 transition">
                                <i class="fa-solid fa-right-from-bracket text-base"></i>
                                <span>Keluar</span>
                            </button>
                        </form>
                    </div>
                </aside>

                <!-- Right Main Content Area for Admin -->
                <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
                    
                    <!-- Top Header Bar -->
                    <header class="h-20 bg-white/80 backdrop-blur-md border-b border-slate-200/80 px-6 sm:px-8 flex items-center justify-between sticky top-0 z-20">
                        <div>
                            <h2 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                                Halo, {{ auth()->user()->name }}! <span class="animate-bounce">👋</span>
                            </h2>
                        </div>

                        <!-- User Profile Top Right -->
                        <div class="flex items-center gap-4">
                            <a href="{{ route('profile.show') }}" class="flex items-center gap-3 pl-4 border-l border-slate-200 hover:opacity-80 transition">
                                <div class="w-10 h-10 rounded-full bg-emerald-500 text-white font-bold flex items-center justify-center text-sm shadow">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                                </div>
                                <div class="hidden sm:block text-left">
                                    <div class="text-xs font-bold text-slate-900">{{ auth()->user()->name }}</div>
                                    <div class="text-[10px] text-emerald-600 font-bold uppercase">Administrator</div>
                                </div>
                            </a>
                        </div>
                    </header>

                    <!-- Page Body -->
                    <main class="p-6 sm:p-8 flex-1">
                        @yield('content')
                    </main>

                    <footer class="py-4 px-8 border-t border-slate-200/60 text-xs text-slate-400 text-center">
                        &copy; {{ date('Y') }} SOBAT Dishub - Sistem Olah & Presensi Magang Terpadu Dinas Perhubungan
                    </footer>
                </div>

            </div>
        @else
            <!-- USER (PESERTA MAGANG) LAYOUT: MOBILE-FIRST -->
            <div class="min-h-screen flex flex-col bg-slate-50 text-slate-800">
                <!-- Top Header for User -->
                <header class="sticky top-0 z-40 bg-white/90 backdrop-blur-md border-b border-slate-200 px-4 py-3 shadow-sm">
                    <div class="max-w-md mx-auto flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <img src="{{ asset('images/logo-dishub.png') }}" alt="Logo Dishub" class="w-9 h-9 object-contain drop-shadow">
                            <div>
                                <span class="font-extrabold text-lg text-slate-900 tracking-tight block leading-none">
                                    SOBAT <span class="text-emerald-600">Dishub</span>
                                </span>
                                <span class="text-[9px] text-slate-400 font-bold uppercase tracking-wider block mt-0.5">Presensi Magang</span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="py-1.5 px-3 rounded-xl bg-slate-100 hover:bg-rose-50 text-slate-600 hover:text-rose-600 border border-slate-200 text-xs font-bold transition flex items-center gap-1.5">
                                    <i class="fa-solid fa-right-from-bracket"></i> Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Bottom Tab Navigation for Interns -->
                    <div class="max-w-md mx-auto mt-2 pt-2 border-t border-slate-100 grid grid-cols-3 gap-1.5 text-center text-xs font-extrabold">
                        <a href="{{ route('presensi.index') }}" 
                           class="py-2 px-2 rounded-xl transition flex items-center justify-center gap-1.5 {{ request()->routeIs('presensi.index') ? 'bg-emerald-500 text-white shadow-md shadow-emerald-500/20' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                            <i class="fa-solid fa-camera-retro"></i>
                            <span>Presensi</span>
                        </a>
                        <a href="{{ route('presensi.leave.index') }}" 
                           class="py-2 px-2 rounded-xl transition flex items-center justify-center gap-1.5 {{ request()->routeIs('presensi.leave.*') ? 'bg-emerald-500 text-white shadow-md shadow-emerald-500/20' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                            <i class="fa-solid fa-file-lines"></i>
                            <span>Izin / Cuti</span>
                        </a>
                        <a href="{{ route('profile.show') }}" 
                           class="py-2 px-2 rounded-xl transition flex items-center justify-center gap-1.5 {{ request()->routeIs('profile.*') ? 'bg-emerald-500 text-white shadow-md shadow-emerald-500/20' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                            <i class="fa-solid fa-user"></i>
                            <span>Profil</span>
                        </a>
                    </div>
                </header>

                <main class="flex-1 max-w-md w-full mx-auto px-4 py-6">
                    @yield('content')
                </main>

                <footer class="py-4 border-t border-slate-200/60 text-center text-xs text-slate-400 bg-white">
                    &copy; {{ date('Y') }} SOBAT Dishub - Dinas Perhubungan
                </footer>
            </div>
        @endif
    @else
        <!-- GUEST LAYOUT (LOGIN) -->
        <main class="flex-1">
            @yield('content')
        </main>
    @endauth

    @stack('scripts')
</body>
</html>
