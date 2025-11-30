<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export Galeri</title>
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
        .thumbnail {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <h1>Data Galeri</h1>
    <p><strong>Tanggal Export:</strong> {{ now()->format('d/m/Y H:i:s') }}</p>
    <p><strong>Total Data:</strong> {{ $galeri->count() }}</p>
    
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Thumbnail</th>
                <th>Judul</th>
                <th>Deskripsi</th>
                <th>Status</th>
                <th>Published At</th>
                <th>Jumlah Gambar</th>
                <th>Dibuat</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($galeri as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>
                    @if($item->getFirstMedia('galeri_images'))
                        <img src="{{ $item->getFirstMedia('galeri_images')->getUrl('thumb') }}" 
                             alt="{{ $item->title }}"
                             class="thumbnail" />
                    @else
                        -
                    @endif
                </td>
                <td>{{ $item->title }}</td>
                <td class="description">{{ $item->description ? \Illuminate\Support\Str::limit($item->description, 100) : '-' }}</td>
                <td>{{ $item->is_published ? 'Published' : 'Draft' }}</td>
                <td>{{ $item->published_at ? $item->published_at->format('d/m/Y H:i') : '-' }}</td>
                <td>{{ $item->getMedia('galeri_images')->count() }}</td>
                <td>{{ $item->created_at->format('d/m/Y H:i') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align: center;">Tidak ada data</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>

