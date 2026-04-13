<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Changed</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f6f8; font-family: Arial, sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f6f8; padding:20px 0;">
        <tr>
            <td align="center">

                <!-- Container -->
                <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.05);">

                    <!-- Header -->
                    <tr>
                        <td style="background:#dc3545; color:#ffffff; padding:20px; text-align:center;">
                            <h2 style="margin:0;">Security Alert</h2>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding:30px; color:#333333;">

                            <p style="margin-top:0;">
                                Hello {{ $name ?? $username ?? 'Admin' }},
                            </p>

                            <p>
                                This is a confirmation that your admin account password has been successfully changed.
                            </p>

                            <!-- Details Table -->
                            <table width="100%" cellpadding="10" cellspacing="0" style="background:#f8f9fa; border-radius:6px; margin:20px 0; border-collapse:collapse;">
                                
                                @if(!empty($username))
                                <tr>
                                    <td style="border-bottom:1px solid #e9ecef;"><strong>Username:</strong></td>
                                    <td style="border-bottom:1px solid #e9ecef;">{{ $username }}</td>
                                </tr>
                                @endif

                                <tr>
                                    <td><strong>Changed at:</strong></td>
                                    <td>
                                        {{ $changedAt }}
                                        @if(!empty($timezone)) ({{ $timezone }}) @endif
                                    </td>
                                </tr>

                            </table>

                            <p>
                                If you made this change, no further action is required.
                            </p>

                            <p style="color:#dc3545;">
                                If you did <strong>not</strong> make this change, please reset your password immediately or contact support.
                            </p>

                            <p>
                                Regards,<br>
                                <strong>Fatafat Team</strong>
                            </p>

                        </td>
                    </tr>

                    <!-- Footer -->
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