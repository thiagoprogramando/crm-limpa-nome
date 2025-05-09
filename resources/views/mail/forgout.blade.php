<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Recuperação de Senha</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #f2f4f6;
                margin: 0;
                padding: 0;
            }
            .email-container {
                max-width: 600px;
                margin: 40px auto;
                background-color: #ffffff;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.05);
                padding: 30px;
            }
            .email-header {
                text-align: center;
                margin-bottom: 30px;
            }
            .email-header h2 {
                color: #333333;
            }
            .email-body {
                color: #555555;
                font-size: 16px;
                line-height: 1.6;
            }
            .email-button {
                margin: 30px 0;
                text-align: center;
            }
            .email-button a {
                background-color: #28a745;
                color: #ffffff !important;
                padding: 12px 24px;
                text-decoration: none;
                border-radius: 6px;
                font-weight: bold;
                display: inline-block;
            }
            .email-footer {
                font-size: 12px;
                color: #999999;
                text-align: center;
                margin-top: 40px;
            }
        </style>
    </head>
    <body>
        <div class="email-container">
            <div class="email-header">
                <h2>Recuperação de Senha</h2>
            </div>
            <div class="email-body">
                <p>Olá {{ $data['toName'] }},</p>
                <p>Recebemos sua solicitação para redefinir a senha da conta {{ $data['toEmail'] }}.</p>
                <p>Utilize o botão abaixo para prosseguir com a redefinição:</p>
                <div class="email-button">
                    <a href="{{ env('APP_URL') }}forgout/{{ $data['token'] }}" style="font-size: 20px; background-color: #0069d9;">Recuperar Minha Senha</a>
                </div>
                <p>Se você não solicitou esta alteração, ignore este e-mail.</p>
                <p>Este código é válido por tempo limitado.</p>
            </div>
            <div class="email-footer">
                <p>{{ env('APP_NAME') }} • {{ env('MAIL_FROM_ADDRESS') }}</p>
            </div>
        </div>
    </body>
</html>
