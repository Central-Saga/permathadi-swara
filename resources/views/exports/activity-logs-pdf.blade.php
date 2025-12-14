<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Activity Logs Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
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

        .header {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Activity Logs Report</h1>
        <p>Generated: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Model</th>
                <th>Event</th>
                <th>Description</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            @foreach($activities as $activity)
            <tr>
                <td>{{ $activity->id }}</td>
                <td>{{ $activity->causer->name ?? 'System' }}</td>
                <td>{{ $activity->subject_type ? class_basename($activity->subject_type) : '-' }}</td>
                <td>{{ ucfirst($activity->event ?? '-') }}</td>
                <td>{{ $activity->description ?? '-' }}</td>
                <td>{{ $activity->created_at->format('d/m/Y H:i:s') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>