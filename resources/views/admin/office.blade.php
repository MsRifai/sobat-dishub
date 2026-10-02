@extends('layouts.app')

@section('title', 'Lokasi Kantor & Jam Kerja Operasional - Admin SOBAT Dishub')

@section('content')
<div class="space-y-6">

    <!-- Page Header Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Konfigurasi Lokasi & Jam Kerja</h1>
            <p class="text-xs text-slate-500 font-semibold mt-1">Atur titik geofencing kantor dan fleksibilitas jam pulang operasional (Hari Senin - Kamis & Hari Jumat)</p>
        </div>
    </div>

    <!-- Alert Success / Errors -->
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
                <span>Terdapat kesalahan pengisian formulir:</span>
            </div>
            <ul class="list-disc list-inside text-[11px] font-semibold text-rose-600">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Form Settings Section -->
        <div class="lg:col-span-1 space-y-6">
            <form action="{{ route('admin.office.update') }}" method="POST" class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-5">
                @csrf

                <div class="border-b border-slate-100 pb-3">
                    <h3 class="font-extrabold text-slate-900 text-base flex items-center gap-2">
                        <i class="fa-solid fa-location-dot text-emerald-500"></i> Titik Geofencing
                    </h3>
                    <p class="text-[11px] text-slate-500 font-medium">Koordinat dan batas radius presensi</p>
                </div>

                <!-- Nama Lokasi -->
                <div>
                    <label for="location_name" class="block text-xs font-extrabold text-slate-700 mb-1">
                        Nama Lokasi Kantor <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="location_name" name="location_name" required
                        value="{{ old('location_name', $office->location_name ?? '') }}"
                        class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <!-- Latitude & Longitude -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="latitude" class="block text-xs font-extrabold text-slate-700 mb-1">
                            Latitude <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="latitude" name="latitude" required
                            value="{{ old('latitude', $office->latitude ?? '') }}"
                            class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-mono font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label for="longitude" class="block text-xs font-extrabold text-slate-700 mb-1">
                            Longitude <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="longitude" name="longitude" required
                            value="{{ old('longitude', $office->longitude ?? '') }}"
                            class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-mono font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>

                <!-- Radius -->
                <div>
                    <label for="radius_meters" class="block text-xs font-extrabold text-slate-700 mb-1">
                        Radius Toleransi (Meter) <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" id="radius_meters" name="radius_meters" min="10" max="5000" required
                        value="{{ old('radius_meters', $office->radius_meters ?? 70) }}"
                        class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <!-- Jam Kerja Operasional Section -->
                <div class="border-t border-b border-slate-100 py-3 space-y-3">
                    <div>
                        <h3 class="font-extrabold text-slate-900 text-sm flex items-center gap-2">
                            <i class="fa-solid fa-clock text-amber-500"></i> Jam Kerja Operasional
                        </h3>
                        <p class="text-[11px] text-slate-500 font-medium">Fleksibilitas batas masuk dan jam pulang</p>
                    </div>

                    <!-- Batas Check-in Masuk -->
                    <div>
                        <label for="check_in_cutoff" class="block text-xs font-extrabold text-slate-700 mb-1">
                            Batas Masuk Tepat Waktu (WIB)
                        </label>
                        <input type="time" id="check_in_cutoff" name="check_in_cutoff" required
                            value="{{ old('check_in_cutoff', substr($office->check_in_cutoff ?? '07:30:00', 0, 5)) }}"
                            class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-mono font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <!-- Jam Pulang Senin - Kamis -->
                    <div>
                        <label for="check_out_start_weekday" class="block text-xs font-extrabold text-slate-700 mb-1">
                            Jam Pulang Minimal (Senin - Kamis)
                        </label>
                        <input type="time" id="check_out_start_weekday" name="check_out_start_weekday" required
                            value="{{ old('check_out_start_weekday', substr($office->check_out_start_weekday ?? '16:00:00', 0, 5)) }}"
                            class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-mono font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <!-- Jam Pulang Hari Jumat (Fleksibilitas Operasional) -->
                    <div class="p-3 bg-amber-50 border border-amber-200 rounded-2xl space-y-1">
                        <label for="check_out_start_friday" class="block text-xs font-extrabold text-amber-900 mb-1 flex items-center gap-1.5">
                            <i class="fa-solid fa-star text-amber-500"></i> Jam Pulang Minimal (Spesial Hari Jumat)
                        </label>
                        <input type="time" id="check_out_start_friday" name="check_out_start_friday" required
                            value="{{ old('check_out_start_friday', substr($office->check_out_start_friday ?? '15:30:00', 0, 5)) }}"
                            class="w-full py-2.5 px-3 bg-white border border-amber-300 rounded-xl text-xs font-mono font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-amber-500">
                        <span class="text-[10px] text-amber-700 font-medium block">Atur jam kepulangan khusus hari Jumat apabila ada penyesuaian jam kerja dinas.</span>
                    </div>

                    <!-- Status Sabtu & Minggu -->
                    <div class="p-3 bg-slate-100 border border-slate-200 rounded-2xl flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-slate-200 text-slate-600 flex items-center justify-center font-bold text-sm shrink-0">
                            <i class="fa-solid fa-umbrella-beach"></i>
                        </div>
                        <div>
                            <div class="text-xs font-extrabold text-slate-800">Sabtu & Minggu: Libur Akhir Pekan</div>
                            <div class="text-[10px] text-slate-500 font-medium">Layanan presensi lokasi dinonaktifkan otomatis pada hari libur akhir pekan.</div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                    class="w-full py-3.5 px-4 bg-emerald-500 hover:bg-emerald-600 text-white font-extrabold text-xs rounded-2xl shadow-md shadow-emerald-500/20 transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>Simpan Perubahan</span>
                </button>
            </form>
        </div>

        <!-- Interactive Map Preview Section -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4 h-full flex flex-col">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <h3 class="font-extrabold text-slate-900 text-base flex items-center gap-2">
                            <i class="fa-solid fa-map-location-dot text-emerald-500"></i> Peta Lokasi Kantor & Lingkaran Radius
                        </h3>
                        <p class="text-[11px] text-slate-500 font-medium">Klik pada peta untuk menyetel ulang titik koordinat kantor secara langsung</p>
                    </div>
                </div>

                <!-- Leaflet Map Div Container -->
                <div id="officeMap" class="w-full flex-1 min-h-[420px] rounded-2xl border border-slate-200 overflow-hidden shadow-inner z-10"></div>
            </div>
        </div>

    </div>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const defaultLat = {{ $office->latitude ?? -7.7956 }};
        const defaultLng = {{ $office->longitude ?? 110.3695 }};
        const defaultRadius = {{ $office->radius_meters ?? 70 }};

        const map = L.map('officeMap').setView([defaultLat, defaultLng], 17);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        let marker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(map);
        let circle = L.circle([defaultLat, defaultLng], {
            color: '#10b981',
            fillColor: '#10b981',
            fillOpacity: 0.2,
            radius: defaultRadius
        }).addTo(map);

        function updateInputs(lat, lng) {
            document.getElementById('latitude').value = lat.toFixed(8);
            document.getElementById('longitude').value = lng.toFixed(8);
            marker.setLatLng([lat, lng]);
            circle.setLatLng([lat, lng]);
        }

        marker.on('dragend', function (e) {
            const position = marker.getLatLng();
            updateInputs(position.lat, position.lng);
        });

        map.on('click', function (e) {
            updateInputs(e.latlng.lat, e.latlng.lng);
        });

        document.getElementById('radius_meters').addEventListener('input', function (e) {
            const r = parseInt(e.target.value) || 70;
            circle.setRadius(r);
        });
    });
</script>
@endpush
