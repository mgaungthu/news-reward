<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Password Reset Code</title>
</head>
<body style="font-family: Arial, sans-serif; background: #f7f7f7; padding: 20px;">

    <div style="max-width: 480px; margin: 0 auto; background: #ffffff; border-radius: 8px; padding: 30px; box-shadow: 0 0 10px rgba(0,0,0,0.08);">

        <h2 style="text-align: center; color: #333; margin-bottom: 20px;">
             Password Reset Request
        </h2>

        <p style="font-size: 15px; color: #555;">
            You requested to reset your password.  
            Please use the following verification code to reset your password:
        </p>

        <div style="text-align: center; margin: 25px 0;">
            <span style="font-size: 32px; letter-spacing: 4px; font-weight: bold; color: #1a73e8;">
                {{ $code }}
            </span>
        </div>

        <p style="font-size: 14px; color: #666;">
            This code will expire in <strong>10 minutes</strong>.  
            If you did not request a password reset, you can safely ignore this email.
        </p>

        <p style="margin-top: 30px; font-size: 13px; color: #999; text-align: center;">
            © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </p>

    </div>

</body>
</html>