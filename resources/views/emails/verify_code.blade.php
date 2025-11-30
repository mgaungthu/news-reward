<h2>Your verification code</h2>
<p>Here is your 6-digit verification code:</p>
<h1 style="font-size:32px; font-weight:bold;">{{ $code }}</h1>
<p>This code expires in 10 minutes.</p>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Email Verification Code</title>
</head>
<body style="font-family: Arial, sans-serif; background: #f7f7f7; padding: 20px;">

    <div style="max-width: 480px; margin: 0 auto; background: #ffffff; border-radius: 8px; padding: 30px; box-shadow: 0 0 10px rgba(0,0,0,0.08);">

        <h2 style="text-align: center; color: #333; margin-bottom: 20px;">
             Email Verification
        </h2>

        <p style="font-size: 15px; color: #555;">
            Please use the following verification code to verify your email:
        </p>

        <div style="text-align: center; margin: 25px 0;">
            <span style="font-size: 32px; letter-spacing: 4px; font-weight: bold; color: #1a73e8;">
                {{ $code }}
            </span>
        </div>

        <p style="font-size: 14px; color: #666;">
            This verification code will expire in <strong>10 minutes</strong>.
        </p>

        <p style="margin-top: 30px; font-size: 13px; color: #999; text-align: center;">
            © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </p>

    </div>

</body>
</html>