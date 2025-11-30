<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Pesan Diterima</title>
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
        .greeting {
            font-size: 18px;
            color: #111827;
            margin-bottom: 20px;
        }
        .message {
            color: #374151;
            margin: 20px 0;
            line-height: 1.8;
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
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 14px;
        }
        .contact-info {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .contact-info h3 {
            margin-top: 0;
            color: #0369a1;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Terima Kasih Atas Pesan Anda</h1>
    </div>
    
    <div class="content">
        <div class="greeting">
            Halo {{ $contactMessage->name }},
        </div>
        
        <div class="message">
            <p>Terima kasih telah menghubungi <strong>Permathadi Swara</strong>. Kami telah menerima pesan Anda dan akan segera menindaklanjutinya.</p>
            
            <p>Berikut adalah ringkasan pesan yang Anda kirimkan:</p>
        </div>
        
        <div class="info-box">
            <div class="info-row">
                <span class="info-label">Subjek:</span>
                <span class="info-value">{{ $contactMessage->subject }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Tanggal:</span>
                <span class="info-value">{{ $contactMessage->created_at->format('d F Y, H:i') }} WITA</span>
            </div>
        </div>
        
        <div class="message">
            <p>Tim kami akan memproses pesan Anda dan akan menghubungi Anda melalui email <strong>{{ $contactMessage->email }}</strong> dalam waktu 1-2 hari kerja.</p>
            
            <p>Jika Anda memiliki pertanyaan mendesak, jangan ragu untuk menghubungi kami kembali.</p>
        </div>
        
        <div class="contact-info">
            <h3>Informasi Kontak</h3>
            <p style="margin: 5px 0;"><strong>Email:</strong> <a href="mailto:mirayasmin34@gmail.com">mirayasmin34@gmail.com</a></p>
            <p style="margin: 5px 0;"><strong>Website:</strong> Permathadi Swara</p>
        </div>
        
        <div class="footer">
            <p><strong>Permathadi Swara</strong></p>
            <p>Sanggar Tabuh Tradisional Bali</p>
            <p style="margin-top: 15px; font-size: 12px; color: #9ca3af;">
                Email ini dikirim otomatis. Mohon jangan membalas email ini.
            </p>
        </div>
    </div>
</body>
</html>

