<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Updated</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f6f8; font-family: Arial, sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f6f8; padding:20px 0;">
        <tr>
            <td align="center">

                <!-- Container -->
                <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.05);">

                    <!-- Header -->
                    <tr>
                        <td style="background:#0d6efd; color:#ffffff; padding:20px; text-align:center;">
                            <h2 style="margin:0;">Profile Updated</h2>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding:30px; color:#333333;">

                            <p style="margin-top:0;">
                                Hello {{ $name ?? $username ?? 'Admin' }},
                            </p>

                            <p>
                                Your admin profile details have been successfully updated. Here are the latest details:
                            </p>

                            <!-- Info Table -->
                            <table width="100%" cellpadding="10" cellspacing="0" style="background:#f8f9fa; border-radius:6px; margin:20px 0; border-collapse:collapse;">
                                
                                <tr>
                                    <td style="border-bottom:1px solid #e9ecef;"><strong>Email:</strong></td>
                                    <td style="border-bottom:1px solid #e9ecef;">{{ $email }}</td>
                                </tr>

                                <tr>
                                    <td style="border-bottom:1px solid #e9ecef;"><strong>Username:</strong></td>
                                    <td style="border-bottom:1px solid #e9ecef;">{{ $username }}</td>
                                </tr>

                                @if(!empty($role))
                                <tr>
                                    <td style="border-bottom:1px solid #e9ecef;"><strong>Role:</strong></td>
                                    <td style="border-bottom:1px solid #e9ecef;">{{ $role }}</td>
                                </tr>
                                @endif

                                <tr>
                                    <td><strong>Updated at:</strong></td>
                                    <td>
                                        {{ $updatedAt }}
                                        @if(!empty($timezone)) ({{ $timezone }}) @endif
                                    </td>
                                </tr>

                            </table>

                            <p style="color:#dc3545;">
                                If you did not request this change, please contact support immediately.
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