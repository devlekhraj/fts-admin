<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Status Updated</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f6f8; font-family: Arial, sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f6f8; padding:20px 0;">
        <tr>
            <td align="center">

                <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.05);">

                    <tr>
                        <td style="background:#0d6efd; color:#ffffff; padding:20px; text-align:center;">
                            <h2 style="margin:0;">Order Status Updated</h2>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:30px; color:#333333;">

                            <p style="margin-top:0;">
                                Hello {{ $customerName ?? 'Customer' }},
                            </p>

                            <p>
                                Your order status has been updated successfully. Please find the latest details below:
                            </p>

                            <table width="100%" cellpadding="10" cellspacing="0" style="background:#f8f9fa; border-radius:6px; margin:20px 0; border-collapse:collapse;">
                                <tr>
                                    <td style="border-bottom:1px solid #e9ecef;"><strong>Order Number:</strong></td>
                                    <td style="border-bottom:1px solid #e9ecef;">{{ $orderNumber }}</td>
                                </tr>
                                <tr>
                                    <td style="@if(!empty($updatedAt)) border-bottom:1px solid #e9ecef; @endif"><strong>Status:</strong></td>
                                    <td style="@if(!empty($updatedAt)) border-bottom:1px solid #e9ecef; @endif">{{ $status }}</td>
                                </tr>
                                @if(!empty($updatedAt))
                                <tr>
                                    <td><strong>Updated at:</strong></td>
                                    <td>{{ $updatedAt }}</td>
                                </tr>
                                @endif
                            </table>

                            <p>
                                If you have any questions, please contact our support team.
                            </p>

                            <p>
                                Thank you for shopping with us.
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