<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification Code</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f6f8; font-family: Arial, sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f6f8; padding:20px 0;">
        <tr>
            <td align="center">

                <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.05);">

                    <tr>
                        <td style="background:#0d6efd; color:#ffffff; padding:20px; text-align:center;">
                            <h2 style="margin:0;">Verify Your Email</h2>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:30px; color:#333333;">

                            <p style="margin-top:0;">
                                Hello {{ $name ?? $username ?? 'Admin' }},
                            </p>

                            <p>Use this verification code to confirm your new admin email address:</p>

                            <div style="text-align:center; margin:20px 0;">
                                <div style="display:inline-block; background:#f8f9fa; border:1px solid #e9ecef; border-radius:6px; padding:14px 22px; font-size:24px; letter-spacing:4px; font-weight:bold;">
                                    {{ $code }}
                                </div>
                            </div>

                            <p>This code expires in {{ $expiresInMinutes }} minutes.</p>

                            <p style="color:#dc3545;">
                                If you did not request this, you can ignore this email.
                            </p>

                            <p>
                                Regards,<br>
                                <strong>Fatafat Team</strong>
                            </p>

                        </td>
                    </tr>

                    <tr>
                        <td style="background:#f1f3f5; text-align:center; padding:15px; font-size:12px; color:#888;">
                            © {{ date('Y') }} Fatafatsewa Pvt Ltd. All rights reserved.
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>
</html>

