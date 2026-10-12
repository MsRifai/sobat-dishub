<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rapor Presensi - {{ $user->name }}</title>
    <style>
        @page {
            margin: 1.5cm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 9pt;
            color: #1e293b;
            line-height: 1.4;
        }

        /* Title Block */
        .header-title {
            margin-bottom: 12px;
        }
        .header-title h2 {
            margin: 0;
            font-size: 14pt;
            font-weight: bold;
            color: #0f172a;
        }
        .header-title p {
            margin: 3px 0 0 0;
            font-size: 8.5pt;
            color: #64748b;
        }

        /* Simple User Info Bar */
        .info-bar {
            margin-bottom: 15px;
            padding: 8px 12px;
            background-color: #f1f5f9;
            border-radius: 6px;
            font-size: 8.5pt;
            color: #334155;
        }
        .info-bar span {
            margin-right: 15px;
        }

        /* Simple Clean Table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        .data-table th {
            background-color: #1e293b;
            color: #ffffff;
            font-size: 8.5pt;
            font-weight: bold;
            padding: 8px 6px;
            border: 1px solid #1e293b;
            text-align: left;
        }
        .data-table td {
            font-size: 8.5pt;
            padding: 7px 6px;
            border: 1px solid #cbd5e1;
            vertical-align: middle;
        }
        .data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }

        /* Status Colors */
        .status-tepat { color: #166534; font-weight: bold; }
        .status-terlambat { color: #7e22ce; font-weight: bold; }
        .status-izin { color: #1d4ed8; font-weight: bold; }
        .status-mock { color: #be123c; font-weight: bold; }
    </style>
</head>
<body>

    <!-- Judul Laporan -->
    <div class="header-title">
        <h2>Rapor Presensi Magang</h2>
        <p>SOBAT Dishub DIY - Tanggal Cetak: {{ \Carbon\Carbon::now('Asia/Jakarta')->translatedFormat('d F Y, H:i') }} WIB</p>
    </div>

    <!-- Info Peserta Singkat -->
    <div class="info-bar">
        <span><strong>Nama:</strong> {{ $user->name }}</span>
        <span><strong>NIM/ID:</strong> {{ $user->username }}</span>
        <span><strong>Instansi/Kampus:</strong> {{ $user->asal_instansi_kampus ?? 'Magang' }}</span>
        <span><strong>Tingkat Kehadiran:</strong> <strong style="color: #059669;">{{ $attendanceRate }}%</strong></span>
    </div>

    <!-- Tabel Absen Simple & Rapi -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;" class="text-center">No</th>
                <th style="width: 110px;">Tanggal</th>
                <th style="width: 70px;" class="text-center">Masuk</th>
                <th style="width: 70px;" class="text-center">Pulang</th>
                <th style="width: 100px;" class="text-center">Status</th>
                <th>Keterangan / Security GPS</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendances as $index => $row)
                <tr>
                    <td class="text-center font-bold">{{ $index + 1 }}</td>
                    <td>{{ $row->date ? $row->date->translatedFormat('d M Y') : '-' }}</td>
                    <td class="text-center">
                        {{ $row->check_in_time ? substr($row->check_in_time, 0, 5) : '--:--' }}
                    </td>
                    <td class="text-center">
                        {{ $row->check_out_time ? substr($row->check_out_time, 0, 5) : '--:--' }}
                    </td>
                    <td class="text-center">
                        @if($row->status === 'tepat_waktu')
                            <span class="status-tepat">Tepat Waktu</span>
                        @elseif($row->status === 'terlambat')
                            <span class="status-terlambat">Terlambat ({{ $row->late_minutes }}m)</span>
                        @elseif(in_array($row->status, ['sakit', 'izin', 'cuti']))
                            <span class="status-izin">{{ strtoupper($row->status) }}</span>
                        @else
                            <span>{{ strtoupper($row->status) }}</span>
                        @endif
                    </td>
                    <td>
                        @if($row->is_mock_location || $row->is_suspicious)
                            <span class="status-mock">Mock / Suspicious GPS</span>
                        @else
                            <span style="color: #475569;">GPS Valid (±{{ $row->gps_accuracy ?? 5 }}m)</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding: 15px; color: #94a3b8;">
                        Belum ada riwayat presensi.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>
