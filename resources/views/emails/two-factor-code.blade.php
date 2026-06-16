<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Код подтверждения</title>
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
            background: linear-gradient(135deg, #17a2b8, #138496);
            padding: 30px;
            text-align: center;
        }
        .email-header h1 {
            color: #ffffff;
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
        .code-box {
            background: linear-gradient(135deg, #17a2b8, #138496);
            padding: 25px;
            text-align: center;
            margin: 25px 0;
            border-radius: 12px;
        }
        .code-box .code {
            font-size: 42px;
            font-weight: 700;
            color: #ffffff;
            letter-spacing: 8px;
            display: block;
            margin: 10px 0;
        }
        .code-box .expires {
            color: rgba(255, 255, 255, 0.9);
            font-size: 14px;
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
            
            <p>Вы пытаетесь войти в свой аккаунт. Для подтверждения личности используйте код ниже:</p>
            
            <div class="code-box">
                <span class="code">{{ $code }}</span>
                <span class="expires">Действителен {{ $expiresMinutes }} минут</span>
            </div>
            
            <div class="warning-box">
                <p>⚠️ <strong>Важно:</strong> Этот код может использоваться только один раз. Не сообщайте его никому.</p>
            </div>
            
            <p>Если вы не запрашивали код подтверждения, просто проигнорируйте это письмо. Ваш аккаунт останется в безопасности.</p>
        </div>
        
        <div class="email-footer">
            <p><span class="logo">🎬 Кино на колёсах</span></p>
            <p>Мобильный кинотеатр там, где нет кинотеатров</p>
            <p>&copy; {{ date('Y') }} Все права защищены</p>
        </div>
    </div>
</body>
</html>
