<p>Hi {{ $emiRequest->name ?? 'there' }},</p>

<p>Your EMI request (#{{ $emiRequest->id }}) has been approved.</p>

@if(!empty($emiRequest->bank?->name))
    <p><strong>Bank:</strong> {{ $emiRequest->bank->name }}</p>
@endif

@if(!empty($emiRequest->product?->name))
    <p><strong>Product:</strong> {{ $emiRequest->product->name }}</p>
@endif

@if(!empty($quotationPdf['url']))
    <p>
        <strong>Quotation PDF:</strong>
        <a href="{{ $quotationPdf['url'] }}">{{ $quotationPdf['url'] }}</a>
    </p>
@endif

<p>Thank you.</p>
