<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }

        .content {
            background-color: #ffffff;
            padding: 30px;
            border: 1px solid #e9ecef;
        }

        .button {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }

        .footer {
            background-color: #f8f9fa;
            padding: 15px;
            text-align: center;
            font-size: 12px;
            color: #6c757d;
            border-radius: 0 0 8px 8px;
        }

        .warning {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>🔑 Password Reset Request</h1>
    </div>

    <div class="content">
        <p>Hello <?= esc($first_name ?? 'User') ?>,</p>

        <p>We received a request to reset your password for your account. If you didn't make this request, you can safely ignore this email.</p>

        <p>To reset your password, click the button below:</p>

        <div style="text-align: center;">
            <a href="<?= esc($reset_link) ?>" class="button">Reset Password</a>
        </div>

        <p>Or copy and paste this link in your browser:</p>
        <p style="word-break: break-all; background-color: #f8f9fa; padding: 10px; border-radius: 4px;">
            <?= esc($reset_link) ?>
        </p>

        <div class="warning">
            <strong>⚠️ Important:</strong>
            <ul>
                <li>This link will expire in 1 hour for security reasons</li>
                <li>If you didn't request this reset, please ignore this email</li>
                <li>Never share this link with anyone</li>
            </ul>
        </div>

        <p>If you're having trouble clicking the button, copy and paste the URL above into your web browser.</p>

        <p>Best regards,<br>
            The MyApp Team</p>
    </div>

    <div class="footer">
        <p>This is an automated email. Please do not reply to this message.</p>
        <p>If you need help, contact our support team.</p>
    </div>
</body>

</html>