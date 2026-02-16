<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Nabil Installment Application Form</title>
    <style>
        @page {
            size: A4;
            margin: 0;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: sans-serif;
            font-size: 14px;
            max-width: 794px;
        }

        #container {
            max-width: 794px;
        }

        #header #logoContainer {
            text-align: right;
            padding: 40px;
        }

        #headerTitle {
            text-align: center;
            font-size: 20px;
            padding: 20px 0;
        }

        .section {
            padding: 0 40px;
        }

        p {
            margin: 0;
            padding: 0;
        }

        .form-line {
            /* force no line break */
            white-space: nowrap;
            margin: 10px 0;
            padding: 10px 0;
            /* background-color: red; */
        }

        .label {
            display: inline-block;
            min-width: 120px;
            vertical-align: bottom;
        }

        .underline {
            display: inline-block;
            /* width: calc(100% - 180px); */
            border-bottom: 1px solid #000;
            vertical-align: bottom;
            overflow: hidden;
            white-space: nowrap;
        }

        .checkbox-table {
            border-collapse: collapse;
            width: 100%;
            font-family: sans-serif;
        }

        .label-checkbox {
            white-space: nowrap;
            vertical-align: top;
            padding-right: 10px;
        }

        .installment-options {
            display: inline-block;
            width: 450px;
        }

        .option-box {
            display: inline-block;
            border: 1px solid #000;
            padding: 2px 8px;
            width: 12px;
            margin-right: 2px;
            text-align: center;
            font-weight: 500;
            background-color: #f9f9f9;
            position: relative;
        }

        .option-box.selected {
            background: #000;
            color: #fff;
        }

        .tick-icon {
            position: absolute;
            bottom: -12px;
            left: 10px;
        }


        /* Credit Card Box Style (PDF-safe) */
        .checkbox-table {
            border-collapse: collapse;
            width: 100%;
            font-family: sans-serif;
        }

        .label-checkbox {
            white-space: nowrap;
            vertical-align: top;
            padding-right: 10px;
        }

        .card-number-group {
            display: inline-block;
            width: auto;
            /* no flex */
        }

        .digit-box {
            display: inline-block;
            width: 20px;
            height: 18px;
            border: 1px solid #000;
            text-align: center;
            line-height: 18px;
            font-weight: bold;
            background-color: #f9f9f9;
        }


        .digit-spacer {
            display: inline-block;
            width: 10px;
            /* spacing between 4-digit groups */
        }

        /* Unique table wrapper for expiry date */
        .expiry-checkbox-table {
            border-collapse: collapse;
            width: 100%;
            font-family: sans-serif;
        }

        /* Unique label style */
        .expiry-label-checkbox {
            white-space: nowrap;
            vertical-align: middle;
            padding-right: 10px;
            font-weight: bold;
            font-size: 14px;
        }

        /* Unique container for expiry digits */
        .expiry-date-container {
            display: inline-block;
            font-size: 0;
            /* eliminate inline-block whitespace */
            vertical-align: middle;
        }

        /* Unique digit box style */
        .expiry-digit-box {
            display: inline-block;
            width: 24px;
            height: 22px;
            border: 1px solid #000;
            margin-right: -1px;
            text-align: center;
            background-color: #f9f9f9;
            font-size: 17px;
        }

        /* Unique format text style */
        .expiry-format-text {
            font-size: 12px;
            margin-left: 8px;
            vertical-align: middle;
            color: #555;
        }
    </style>
</head>

<body>
    <div id="container">
        <div id="header">
            <div id="logoContainer">
                <img src="logo/nabil.png" alt="Nabil Bank Logo" height="40" />
            </div>
            <div id="headerTitle">
                <strong>Nabil Installment Application Form</strong>
            </div>
        </div>

        <div class="section">
            <div style="padding: 10px 0;">
                <p>I hereby apply for Nabil Installment Loan as following:</p>
            </div>

            <div class="form-line">
                <div class="label" style="width: 156px">Name of the cardholder:</div>
                <div class="underline" style="width: 544px">{{ $card_holder_name }}</div>
            </div>

            @php
                $cardNumber = $card_number ?? '';
            @endphp
            <div class="form-line-checkbox">
                <table class="checkbox-table">
                    <tr>
                        <td class="label-checkbox">Nabil Credit Card Number:</td>
                        <td>
                            <div class="card-number-group">
                                @foreach (str_split($cardNumber) as $index => $digit)
                                    <div class="digit-box">{{ $digit }}</div>
                                    @if (($index + 1) % 4 == 0 && $index + 1 !== strlen($cardNumber))
                                        <div class="digit-spacer"></div>
                                    @endif
                                @endforeach
                            </div>

                        </td>
                    </tr>
                </table>
            </div>
            <div class="form-line-checkbox" style="padding-top: 14px;">
                <table class="expiry-checkbox-table">
                    <tr>
                        <td class="" style="width: 100px; vertical-align: middle;">Expiry Date:</td>
                        <td>
                            <div class="expiry-date-container">
                                @foreach (str_split($expiry_date ?? '') as $digit)
                                    <div class="expiry-digit-box">{{ $digit }}</div>
                                @endforeach
                                <span class="expiry-format-text">(mm/yy)</span>
                            </div>
                            {{-- <div class="expiry-date-container">
                                <div class="expiry-digit-box">0</div>
                                <div class="expiry-digit-box">2</div>
                                <div class="expiry-digit-box">2</div>
                                <div class="expiry-digit-box">6</div>
                                <span class="expiry-format-text">(mm/yy)</span>
                            </div> --}}
                        </td>
                    </tr>
                </table>
            </div>
            <div>
                <div class="form-line" style="float: left; width: 350px;">
                    <div class="label">Telephone Number:</div>
                    <div class="underline" style="width: 210px;">&nbsp;</div>
                </div>
                <div class="form-line" style="float: left; width: 350px;">
                    <div class="label">Mobile Number:</div>
                    <div class="underline" style="width: 224px;">{{ $mobile_no }}</div>
                </div>
                <div style="clear: both;">

                </div>
            </div>

            <div class="form-line">
                <div class="label" style="width: 230px;">Name and address of the Merchant:</div>
                <div class="underline" style="width: 470px">{{ $name_address_merchant }}</div>
                <br>
                <div class="underline" style="width: 702px; height: 30px;"></div>
            </div>

            <div>
                <p>Details of Item Purchased:</p>
                <div>
                    <div class="form-line">
                        <div class="label">Name of the Item:</div>
                        <div class="underline" style="width: 576px;">{{ $product_name }}</div>
                    </div>
                    <div class="form-line">
                        <div class="label">Manufactured By:</div>
                        <div class="underline" style="width: 576px;">{{ $manufacturer }}</div>
                    </div>
                    <div class="form-line">
                        <div class="label" style="width: 150px;">Model Number/ Name:</div>
                        <div class="underline" style="width: 544px;">{{ $model_no }}</div>
                    </div>
                    <div class="form-line">
                        <div class="label">Serial Number: </div>
                        <div class="underline" style="width: 572px;">{{ $serial_no }}</div>
                    </div>
                    <div class="form-line">
                        <div class="label">Amount of Nabil Installment Loan: Rs.: </div>
                        <div class="underline" style="width: 450px;">{{ $installment_amount }}</div>
                    </div>
                    <div class="form-line">
                        <div class="label">Amount in Words: </div>
                        <div class="underline" style="width: 570px;">{{ $installment_amount_words }}</div>
                    </div>
                    <div class="form-line-checkbox">
                        <table class="checkbox-table">
                            <tr>
                                <td class="label-checkbox">Choose Installment tenure (Months):</td>
                                <td>
                                    <div class="installment-options">
                                        @foreach ($emi_tenures as $tenure)
                                            <div class="option-box @if ($tenure == $selected_tenure) selected @endif ">
                                                {{ $tenure }}</div>
                                        @endforeach
                                        {{-- <div class="option-box">12</div>
                                        <div class="option-box selected">18</div> --}}
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>


                </div>
            </div>
            <div style="padding: 20px 0;">
                <p>I hereby declare that I have read and understood terms and conditions of Nabil Bank Ltd. menthioned
                    overleaf
                    governimg Nabil Installment product and credit card I agree to abide by these rules.
                </p>
            </div>
            <div>
                <div class="signature-row"
                    style="padding-bottom:10px; position: relative; margin-bottom: 10px; border-bottom: 2px solid #050505;">
                    <div style="height: 80px; line-height: 80px; font-size: 16px;">
                        {{ $signature_text ?? $card_holder_name ?? 'N/A' }}
                    </div>
                    <label>Signature of Cardholder:</label>
                    <div style="float: right;">
                        <label style="margin-left: 40px;">Date:</label>
                        <span class="input-field" style="width: 100px;">{{ $date }}</span>
                    </div>
                    <div style="clear: both;"></div>
                </div>
            </div>
            <div>
                <p style="padding: 10px 0;"><i>Note:</i></p>
                <p><i>Please leave this for with the merchant or send to Nabil Card Division, Kantipath, Kathmandu.</i>
                </p>
                <p><i>We hereby declare that the information provided above is correct and true to the best of our
                        knowledge</i></p>
            </div>
        </div>
    </div>
</body>

</html>
