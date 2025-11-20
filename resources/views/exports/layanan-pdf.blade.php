<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export Layanan</title>
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
        .description {
            max-width: 300px;
            word-wrap: break-word;
        }
    </style>
</head>
<body>
    <h1>Data Layanan</h1>
    <p><strong>Tanggal Export:</strong> {{ now()->format('d/m/Y H:i:s') }}</p>
    <p><strong>Total Data:</strong> {{ $layanans->count() }}</p>
    
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Layanan</th>
                <th>Slug</th>
                <th>Deskripsi</th>
                <th>Harga</th>
                <th>Status</th>
                <th>Dibuat</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($layanans as $index => $layanan)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $layanan->name }}</td>
                <td>{{ $layanan->slug ?? '-' }}</td>
                <td class="description">{{ $layanan->description ? \Illuminate\Support\Str::limit($layanan->description, 100) : '-' }}</td>
                <td>{{ $layanan->price ? 'Rp ' . number_format($layanan->price, 0, ',', '.') : '-' }}</td>
                <td>{{ $layanan->is_active ? 'Aktif' : 'Tidak Aktif' }}</td>
                <td>{{ $layanan->created_at->format('d/m/Y H:i') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center;">Tidak ada data</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>

