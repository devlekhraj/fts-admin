<p>
    Dear {{ $emiRequest?->name ?? ($emiRequest?->user?->name ?? 'Customer') }},
</p>

<p>
    Thank you for your interest in our EMI service and for taking the time to submit your application.
</p>

<p style="margin: 0; padding:0">
    After a careful review of your request, we regret to inform you that your EMI application could not be approved at
    this time.
</p>

@if (!empty($emiRequest?->id))
    <p style="margin: 0; padding:0">
        <strong>Application Reference ID:</strong> #EMIR-{{ $emiRequest->id }}
    </p>
@endif

@if (!empty($reason))
    <p>
        <strong>Reason:</strong> {{ $reason }}
    </p>
@endif

<p style="margin: 0; padding:0">
    This decision was made based on our current eligibility assessment and internal verification process.
</p>

<p style="margin: 0; padding:0">
    Please note that this does not permanently affect your ability to apply again in the future. You may reapply once
    the necessary requirements or eligibility conditions are met.
</p>

<p>
    If you have any questions or would like further clarification regarding your application status, please feel free to
    contact our support team.
</p>

<p>
    We appreciate your understanding and thank you for choosing us.
</p>

<p>
    Best regards,<br>
    <strong>Fatafat Team</strong>
</p>

<p>© {{ date('Y') }} Fatafatsewa Pvt Ltd. All rights reserved.</p>
