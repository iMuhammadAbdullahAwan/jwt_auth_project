<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Changed</title>
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
            background-color: #d4edda;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }

        .content {
            background-color: #ffffff;
            padding: 30px;
            border: 1px solid #e9ecef;
        }

        .footer {
            background-color: #f8f9fa;
            padding: 15px;
            text-align: center;
            font-size: 12px;
            color: #6c757d;
            border-radius: 0 0 8px 8px;
        }

        .alert {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }

        .success-icon {
            font-size: 48px;
            color: #28a745;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="success-icon">✅</div>
        <h1>Password Changed Successfully</h1>
    </div>

    <div class="content">
        <p>Hello <?= esc($first_name) ?>,</p>

        <p>Your password has been successfully changed for your account.</p>

        <p><strong>Details:</strong></p>
        <ul>
            <li><strong>Date & Time:</strong> <?= esc($time) ?></li>
            <li><strong>IP Address:</strong> <?= esc($ip) ?></li>
        </ul>

        <div class="alert">
            <strong>⚠️ Security Notice:</strong><br>
            If you did not make this change, please contact our support team immediately and consider the following steps:
            <ul>
                <li>Change your password again</li>
                <li>Check for any suspicious account activity</li>
                <li>Review your account security settings</li>
            </ul>
        </div>

        <p>For your security, we recommend:</p>
        <ul>
            <li>Using a strong, unique password</li>
            <li>Not sharing your password with anyone</li>
            <li>Regularly updating your password</li>
        </ul>

        <p>Thank you for keeping your account secure!</p>

        <p>Best regards,<br>
            The MyApp Team</p>
    </div>

    <div class="footer">
        <p>This is an automated security notification. Please do not reply to this message.</p>
        <p>If you need help, contact our support team.</p>
    </div>
</body>

</html>