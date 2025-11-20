<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export Langganan</title>
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
        .notes {
            max-width: 200px;
            word-wrap: break-word;
        }
    </style>
</head>
<body>
    <h1>Data Langganan</h1>
    <p><strong>Tanggal Export:</strong> {{ now()->format('d/m/Y H:i:s') }}</p>
    <p><strong>Total Data:</strong> {{ $subscriptions->count() }}</p>
    
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Anggota</th>
                <th>Email</th>
                <th>Layanan</th>
                <th>Tanggal Mulai</th>
                <th>Tanggal Berakhir</th>
                <th>Status</th>
                <th>Catatan</th>
                <th>Dibuat</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($subscriptions as $index => $subscription)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $subscription->anggota->user->name ?? '-' }}</td>
                <td>{{ $subscription->anggota->user->email ?? '-' }}</td>
                <td>{{ $subscription->layanan->name ?? '-' }}</td>
                <td>{{ $subscription->start_date->format('d/m/Y') }}</td>
                <td>{{ $subscription->end_date ? $subscription->end_date->format('d/m/Y') : '-' }}</td>
                <td>{{ ucfirst($subscription->status) }}</td>
                <td class="notes">{{ $subscription->notes ?? '-' }}</td>
                <td>{{ $subscription->created_at->format('d/m/Y H:i') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="9" style="text-align: center;">Tidak ada data</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>

