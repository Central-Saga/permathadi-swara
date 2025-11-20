<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export Anggota</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .address {
            max-width: 200px;
            word-wrap: break-word;
        }
        .notes {
            max-width: 200px;
            word-wrap: break-word;
        }
    </style>
</head>
<body>
    <h1>Data Anggota</h1>
    <p><strong>Tanggal Export:</strong> {{ now()->format('d/m/Y H:i:s') }}</p>
    <p><strong>Total Data:</strong> {{ $anggotas->count() }}</p>
    
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Email</th>
                <th>Telepon</th>
                <th>Alamat</th>
                <th>Tanggal Lahir</th>
                <th>Tanggal Registrasi</th>
                <th>Status</th>
                <th>Catatan</th>
                <th>Dibuat</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($anggotas as $index => $anggota)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $anggota->user->name ?? '-' }}</td>
                <td>{{ $anggota->user->email ?? '-' }}</td>
                <td>{{ $anggota->telepon }}</td>
                <td class="address">{{ $anggota->alamat ?? '-' }}</td>
                <td>{{ $anggota->tanggal_lahir ? $anggota->tanggal_lahir->format('d/m/Y') : '-' }}</td>
                <td>{{ $anggota->tanggal_registrasi->format('d/m/Y') }}</td>
                <td>{{ $anggota->status }}</td>
                <td class="notes">{{ $anggota->catatan ?? '-' }}</td>
                <td>{{ $anggota->created_at->format('d/m/Y H:i') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="10" style="text-align: center;">Tidak ada data</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>

