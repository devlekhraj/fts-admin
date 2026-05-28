<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Completed</title>
</head>
<body>

    @php($paymentType = (string) ($order?->payment_type ?? ''))

    <p>Dear {{ $customerName ?? 'Customer' }},</p>

    <p>We are pleased to inform you that your order has been successfully completed.</p>

    <p><strong>Order Number:</strong> {{ $orderNumber }}</p>

    <p>
        Thank you for trusting us and choosing our service. We truly appreciate your support and hope you are satisfied with your order.
    </p>

    <p>If you have any questions or need assistance, please feel free to contact us anytime.</p>

    <p>
        Best regards,<br>
        <strong>Fatafat Team</strong>
    </p>

    <p>© {{ date('Y') }} Fatafatsewa Pvt Ltd. All rights reserved.</p>

</body>
</html>
