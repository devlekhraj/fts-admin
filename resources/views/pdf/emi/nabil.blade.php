<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Nabil EMI Application</title>
</head>
<body>
    <h1>Nabil EMI Application</h1>
    <p>Applicant: {{ $bankData->applicantName ?? 'N/A' }}</p>
    <p>Product: {{ $bankData->productName ?? 'N/A' }}</p>
    <p>Installment Amount: {{ $bankData->installmentAmount ?? '0' }}</p>
</body>
</html>

