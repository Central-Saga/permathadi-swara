<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesan Kontak Baru</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #ea580c 0%, #dc2626 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            background: #ffffff;
            padding: 30px;
            border: 1px solid #e5e7eb;
            border-top: none;
            border-radius: 0 0 8px 8px;
        }
        .info-box {
            background: #f9fafb;
            border-left: 4px solid #ea580c;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .info-row {
            margin: 10px 0;
        }
        .info-label {
            font-weight: 600;
            color: #6b7280;
            display: inline-block;
            min-width: 100px;
        }
        .info-value {
            color: #111827;
        }
        .message-box {
            background: #f9fafb;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border: 1px solid #e5e7eb;
        }
        .message-content {
            color: #374151;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Pesan Kontak Baru</h1>
    </div>
    
    <div class="content">
        <p>Anda menerima pesan kontak baru dari website Permathadi Swara.</p>
        
        <div class="info-box">
            <div class="info-row">
                <span class="info-label">Nama:</span>
                <span class="info-value">{{ $contactMessage->name }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Email:</span>
                <span class="info-value">
                    <a href="mailto:{{ $contactMessage->email }}">{{ $contactMessage->email }}</a>
                </span>
            </div>
            @if($contactMessage->phone)
            <div class="info-row">
                <span class="info-label">Telepon:</span>
                <span class="info-value">{{ $contactMessage->phone }}</span>
            </div>
            @endif
            <div class="info-row">
                <span class="info-label">Subjek:</span>
                <span class="info-value">{{ $contactMessage->subject }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Tanggal:</span>
                <span class="info-value">{{ $contactMessage->created_at->format('d F Y, H:i') }} WITA</span>
            </div>
        </div>
        
        <div class="message-box">
            <h3 style="margin-top: 0; color: #111827;">Pesan:</h3>
            <div class="message-content">{!! $contactMessage->message !!}</div>
        </div>
        
        <div class="footer">
            <p>Email ini dikirim otomatis dari sistem Permathadi Swara.</p>
            <p>Jangan membalas email ini. Untuk membalas, gunakan email: <a href="mailto:{{ $contactMessage->email }}">{{ $contactMessage->email }}</a></p>
        </div>
    </div>
</body>
</html>

