<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export Role</title>
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
        .permission-list {
            max-width: 300px;
            word-wrap: break-word;
        }
    </style>
</head>
<body>
    <h1>Data Role</h1>
    <p><strong>Tanggal Export:</strong> {{ now()->format('d/m/Y H:i:s') }}</p>
    <p><strong>Total Data:</strong> {{ $roles->count() }}</p>
    
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Role</th>
                <th>Permission</th>
                <th>Jumlah Permission</th>
                <th>Dibuat</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($roles as $index => $role)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $role->name }}</td>
                <td class="permission-list">{{ $role->permissions->pluck('name')->implode(', ') ?: '-' }}</td>
                <td>{{ $role->permissions->count() }}</td>
                <td>{{ $role->created_at->format('d/m/Y H:i') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align: center;">Tidak ada data</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>

