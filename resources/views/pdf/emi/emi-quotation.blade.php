<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
@php
    $letterPadPath = public_path('images/letter-pad.jpg');
    $letterPadDataUri = '';
    if (is_string($letterPadPath) && $letterPadPath !== '' && file_exists($letterPadPath)) {
        $bytes = @file_get_contents($letterPadPath);
        if (is_string($bytes) && $bytes !== '') {
            $letterPadDataUri = 'data:image/jpeg;base64,' . base64_encode($bytes);
        }
    }

    $stampPath = public_path('images/fatafat-stamp.png');
    $stampDataUri = '';
    if (is_string($stampPath) && $stampPath !== '' && file_exists($stampPath)) {
        $stampBytes = @file_get_contents($stampPath);
        if (is_string($stampBytes) && $stampBytes !== '') {
            $stampDataUri = 'data:image/png;base64,' . base64_encode($stampBytes);
        }
    }
@endphp
<style>
    @page {
        margin: 0px;
        font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
        font-size: 14px
    }

    html,
    body {
        margin: 0;
        padding: 0;
        font-size: 14px;
        background-image: url("{{ $letterPadDataUri !== '' ? $letterPadDataUri : '' }}");
        background-repeat: no-repeat;
        background-position: top left;
        /* Fit the letter pad exactly to the PDF page */
        background-size: 100% 100%;
    }
    .pdf-wrapper {
        position: relative;
    }

    .pdf--content {
        padding: 10px 90px;
    }


    .bordered-table {
        border-collapse: collapse;
        font-size: 14px !important
    }

    .bordered-table td,
    .bordered-table th {
        border: 1px solid #ddd;
        padding: 4px;
        text-align: left;
    }

    /* .bordered-table tr:nth-child(even) {
        background-color: #f2f2f2;
    } */

    .terms-conditions{
      font-size: 14px; 
    }

    .customer--details{
        margin-top: 10px;
      font-size: 15px
    }
</style>

<body>
    <div class="pdf-wrapper">
        {{-- <img src="{{ asset('/website/images/letter-pad.jpg') }}" alt="" style="width:100%"> --}}
        <div class="pdf--content" style="margin-top:200px">
            <div>
                <p><strong>To</strong></p>
                <p><strong>{{ $emiRequest->emiBank?->name ?? $emiRequest->requestBank?->bank?->name ?? '' }}</strong></p>
                <p>Card Department</p>
            </div>

            <div style="margin-top: 20px">
                Dear Concern,<br><br>

                <div style="text-align: center !important;">
                    This is to inform you this below illustration is for the finance amount for
                    <strong>{{ $emiRequest->product->name }}</strong>
                </div>
                <br>
                <table width="80%" align="center" class="bordered-table">
                    <tr>
                        <th>Quotation For</th>
                        <td>
                            <span style="font-size: 14px; font-weight:bold">{{ $emiRequest->product->name }}</span>
                            @if ($emiRequest->product_attributes)
                                <br>
                                @foreach ($emiRequest->product_attributes as $key => $attr)
                                    <span><strong>{{ $key }}</strong>: {{ $attr }}</span><br>
                                @endforeach
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Product Price</th>
                        <td>Rs. {{ $emiRequest->product_price }} /- </td>
                    </tr>
                    <tr>
                        <th>Down Payment</th>
                        <td>NPR {{ $emiRequest->down_payment }} /-</td>
                    </tr>

                    <tr>
                        <th>Finance Amount</th>
                        <td>NPR {{ $emiRequest->finance_amount }} /-</td>
                    </tr>

                    <tr>
                        <th>Duration in Month</th>
                        <td> {{ $emiRequest->emi_mode }} Months</td>
                    </tr>

                    <tr>
                        <th>EMI Per Month</th>
                        <td>NPR {{ $emiRequest->emi_per_month }} /-</td>
                    </tr>
                </table>

                <div class="customer--details" style="margin-top:20px">
                  <u><strong>Customer Details:</strong></u>
                  <br>
                  <span><strong>Full Name</strong>: {{ $emiRequest->name }}</span><br>
                  <span><strong>Contact Number</strong>: {{ $emiRequest->contact_number }}</span><br>
                  <span><strong>Email</strong>: {{ $emiRequest->email }}</span><br>
                  <span><strong>Current Address</strong>: {{ $emiRequest->address }}</span><br>
                  <span><strong>Date</strong>: {{ $emiRequest->created_at->format('jS \\of F Y h:i:s A') }}</span><br><br>
                  @if($stampDataUri !== '')
                      <img src="{{ $stampDataUri }}" width="130px" alt="">
                  @endif
                </div>
                <hr>

                <div class="terms-and-conditions" >
                  <span style="font-weight:bold; font-size: 18px">Terms & Conditions</span><br>
                  <span class="terms-conditions">* The Quotation form is valid up to 7 days for the issued date.</span><br>
                  <span class="terms-conditions">* Customer shall not pay more than MRP amount.</span><br>
                  <span class="terms-conditions">* EMI amount is the amount after dividing the finance amount by the duration.</span><br>

                </div>
            </div>
        </div>
        {{-- <img src="{{ asset('/website/images/letter-pad-footer.png') }}" alt="" style="width:100%; position: absolute;
    left: 0;
    top: 905px !important;"> --}}
    </div>
</body>

</html>
