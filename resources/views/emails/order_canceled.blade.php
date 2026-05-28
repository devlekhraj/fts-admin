<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Canceled</title>
</head>
<body>

    <p>Dear {{ $customerName ?? 'Customer' }},</p>

    <p>We regret to inform you that your order has been canceled.</p>

    <p><strong>Order Number:</strong> {{ $orderNumber }}</p>

    @if(!empty($reason))
        <p><strong>Reason:</strong> {{ $reason }}</p>
    @endif

    <p>If you have any questions or need assistance, please feel free to contact us anytime.</p>

    <p>
        Best regards,<br>
        <strong>Fatafat Team</strong>
    </p>

    <p>© {{ date('Y') }} Fatafatsewa Pvt Ltd. All rights reserved.</p>

</body>
</html>
