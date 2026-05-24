<p>Hi {{ $emiRequest->name ?? 'there' }},</p>

<p>We’re sorry — your EMI request (#{{ $emiRequest->id }}) has been rejected.</p>

<p><strong>Reason:</strong><br>{{ $reason }}</p>

<p>If you believe this was a mistake or you’d like help applying again, please reply to this email.</p>

<p>Thank you.</p>

