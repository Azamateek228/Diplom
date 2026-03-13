<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Сброс пароля</title>
    <style>
        body {
            font-family: 'Montserrat', Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        .email-header {
            background: linear-gradient(135deg, #ffc107, #ffca2c);
            padding: 30px;
            text-align: center;
        }
        .email-header h1 {
            color: #0b0b0f;
            margin: 0;
            font-size: 24px;
        }
        .email-body {
            padding: 40px 30px;
        }
        .email-body h2 {
            color: #333;
            font-size: 20px;
            margin-top: 0;
        }
        .email-body p {
            color: #666;
            line-height: 1.6;
            font-size: 15px;
        }
        .reset-button {
            display: inline-block;
            background: linear-gradient(135deg, #ffc107, #ffca2c);
            color: #0b0b0f !important;
            text-decoration: none;
            padding: 16px 40px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 16px;
            margin: 25px 0;
            text-align: center;
        }
        .reset-button:hover {
            background: linear-gradient(135deg, #ffca2c, #ffd357);
        }
        .token-box {
            background: #f8f9fa;
            border: 2px dashed #ffc107;
            padding: 15px;
            text-align: center;
            margin: 20px 0;
            border-radius: 8px;
            word-break: break-all;
        }
        .token-box .token {
            font-size: 14px;
            color: #333;
            font-family: monospace;
        }
        .warning-box {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .warning-box p {
            color: #856404;
            margin: 0;
            font-size: 14px;
        }
        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #17a2b8;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .info-box p {
            color: #0c5460;
            margin: 0;
            font-size: 14px;
        }
        .email-footer {
            background: #f8f9fa;
            padding: 20px 30px;
            text-align: center;
            border-top: 1px solid #e9ecef;
        }
        .email-footer p {
            color: #999;
            font-size: 13px;
            margin: 5px 0;
        }
        .logo {
            color: #ffcc00;
            font-weight: 700;
            font-size: 18px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>🎬 Кино на колёсах</h1>
        </div>
        
        <div class="email-body">
            <h2>Здравствуйте, {{ $userName }}!</h2>
            
            <p>Вы (или кто-то другой) запросили сброс пароля для аккаунта, связанного с этим email.</p>
            
            <p style="text-align: center;">
                <a href="{{ url('/reset-password/' . $token) }}" class="reset-button">Сбросить пароль</a>
            </p>
            
            <p>Или скопируйте и вставьте эту ссылку в браузер:</p>
            
            <div class="token-box">
                <span class="token">{{ url('/reset-password/' . $token) }}</span>
            </div>
            
            <div class="info-box">
                <p>⏰ <strong>Срок действия:</strong> Ссылка действительна в течение {{ $expiresHours }} часов.</p>
            </div>
            
            <div class="warning-box">
                <p>⚠️ <strong>Важно:</strong> Если вы не запрашивали сброс пароля, просто проигнорируйте это письмо. Ваш пароль останется без изменений.</p>
            </div>
        </div>
        
        <div class="email-footer">
            <p><span class="logo">🎬 Кино на колёсах</span></p>
            <p>Мобильный кинотеатр там, где нет кинотеатров</p>
            <p>&copy; {{ date('Y') }} Все права защищены</p>
        </div>
    </div>
</body>
</html>
