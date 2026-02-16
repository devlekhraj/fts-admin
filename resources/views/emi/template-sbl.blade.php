<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>SBL EMI Loan Application</title>
    <style>
        @page {
            size: A4;
            margin: 0;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: sans-serif;
            font-size: 13px;
            width: 794px;
            background: #fef6e6;
        }

        .header {
            background: #f9b804;
            color: #000;
            padding: 20px 20px 10px 20px;
        }


        .header img {
            height: 40px;
            margin-right: 15px;
        }

        .header .header-title {
            font-size: 22px;
            margin: 0;
            padding: 0;
            margin-top: 6px;
        }

        .header h1 {
            /* font-size: 20px; */
            margin: 10px 0 0 0;
            font-weight: bold;
        }

        .form-wrapper {
            padding: 20px 25px;
        }

        .form-box {
            border: 2px solid #e39f27;
            border-radius: 10px;
            padding: 15px 20px;
            background: #fff7e5;
        }

        .section-title {
            font-weight: bold;
            margin: 20px 0 10px;
            border-bottom: 1px solid #d0a134;
            padding-bottom: 2px;
        }

        .section-header {
            /* background: #f6d390; */
            /* font-weight: bold; */
            /* padding: 5px; */
            /* border: 1px solid #e39f27; */
            text-align: center;
            /* margin-top: 15px; */
            /* margin-bottom: 5px; */
            font-size: 14px;
            font-weight: 600;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            padding: 5px 0;
            margin: 0;
            vertical-align: top;
            font-size: 14px;
        }

        .cc-box {
            display: inline-block;
            border: 1px solid #e39f27;
            border-right: 0;
            margin-right: -4px;
            width: 20px;
            height: 20px;
            text-align: center;
            line-height: 20px;
            font-size: 14px;
        }

        .cc-box:nth-child(4) {
            border-right: 1px solid #e39f27;
        }

        .declaration {
            margin-top: 10px;
            font-size: 15px;
        }

        .sub-box {
            border: 1px solid #e39f27;
            border-radius: 8px;
            padding: 10px;
            background: #fffaef;
            /* margin-top: 10px; */
        }

        .input-field {
            border: 1px solid #e39f27;
            padding: 4px 7px;
            border-radius: 5px;
            background: #fff;
        }

        .input-underline {
            border-bottom: 1px solid #d0a134;
        }

        p {
            padding: 0;
            margin: 0;
        }

        .selected-tenure {
            background: #d0a134;
            color: #fff;
        }
    </style>
</head>

<body>
    <div class="header">
        <img src="logo/sbl-logo.png" alt="Logo">
        <p class="header-title">Siddhartha Credit Card EMI Loan Application Form</h1>
        <div style="clear: both;"></div>
    </div>

    <div class="form-wrapper">
        <div class="form-box">
            <p><strong>I hereby apply for Siddhartha Credit Card EMI Loan as below:</strong></p>
            <table>
                <tr>
                    <td style="width: 154px;vertical-align: middle;">
                        <p>Name of the cardholder:</p>
                    </td>
                    <td>
                        <div class="input-field">{{ $card_holder_name }}</div>
                    </td>
                </tr>
            </table>
            <table>
                <tr>
                    <td style="width: 134px;vertical-align: middle;">
                        <p>SBL Credit Card No.:</p>
                    </td>
                    <td>
                        {{-- <div style="padding-top: 20px;">
                            @foreach (str_split($card_number) as $index => $digit)
                                <div class="cc-box">{{ $digit }}</div>

                           
                                @if (($index + 1) % 4 == 0 && $index + 1 !== strlen($card_number))
                                    <div style="display: inline-block; margin-right: 10px;"></div>
                                @endif
                            @endforeach
                        </div> --}}

                        <div style="padding-top: 20px;">
                            @foreach (array_chunk(str_split($card_number), 4) as $groupIndex => $group)
                                <div style="display: inline-block; margin-right: {{ $groupIndex < 3 ? '10px' : '0' }};">
                                    @foreach ($group as $digit)
                                        <div class="cc-box">{{ $digit }}</div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>


                        {{-- <div style="padding-top: 20px;">
                            <div style="display: inline-block; margin-right: 10px;">
                                <div class="cc-box">4</div>
                                <div class="cc-box">5</div>
                                <div class="cc-box">7</div>
                                <div class="cc-box">1</div>
                            </div>
                            <div style="display: inline-block; margin-right: 10px;">
                                <div class="cc-box">2</div>
                                <div class="cc-box">3</div>
                                <div class="cc-box">4</div>
                                <div class="cc-box">5</div>
                            </div>
                            <div style="display: inline-block; margin-right: 10px;">
                                <div class="cc-box">6</div>
                                <div class="cc-box">7</div>
                                <div class="cc-box">8</div>
                                <div class="cc-box">9</div>
                            </div>
                            <div style="display: inline-block;">
                                <div class="cc-box">0</div>
                                <div class="cc-box">1</div>
                                <div class="cc-box">2</div>
                                <div class="cc-box">3</div>
                            </div>
                        </div> --}}
                    </td>


                    <td style="width: 66px;vertical-align: middle;">
                        <p>Exp. Date:</p>
                    </td>
                    <td style="width: 107px;vertical-align: middle;">
                        <div style="padding-top: 10px;">
                            <div class="cc-box">{{ $expiry_date[0] }}</div>
                            <div class="cc-box">{{ $expiry_date[1] }}</div>
                            <div class="cc-box">{{ $expiry_date[2] }}</div>
                            <div class="cc-box">{{ $expiry_date[3] }}</div>
                        </div>
                        {{-- <div style="padding-top: 10px;">
                            @foreach (str_split($expiry_date ?? '') as $digit)
                                <div class="expiry-digit-box">{{ $digit }}</div>
                            @endforeach
                        </div> --}}

                    </td>
                </tr>
            </table>
            <table>
                <tr>
                    <td style="width: 70px;vertical-align: middle;">
                        <p>Address:</p>
                    </td>
                    <td>
                        <div class="input-field">{{ $address }}</div>
                    </td>
                </tr>
            </table>
            <table>
                <tr>
                    <td style="width:100px;vertical-align: middle;">
                        <p>Telephone No.:</p>
                    </td>
                    <td style="width: 100px;">
                        <div class="input-field">{{ $telephone_no }}</div>
                    </td>
                    <td style="width: 80px; padding-left: 15px;vertical-align: middle;">
                        <p>Mobile No.:</p>
                    </td>
                    <td>
                        <div class="input-field">{{ $mobile_no }}</div>
                    </td>
                    <td style="width: 50px; padding-left: 15px; vertical-align: middle;">
                        <p>Email:</p>
                    </td>
                    <td>
                        <div class="input-field">{{ $email }}</div>
                    </td>
                </tr>
            </table>

            <p class="section-title">Details of item purchased:</p>
            <table>
                <tr>
                    <td style="width: 235px;vertical-align: middle;">
                        <p>Name and address of the merchant:</p>
                    </td>
                    <td>
                        <div class="input-field">{{ $name_address_merchant }}</div>
                    </td>
                </tr>
            </table>
            <table>
                <tr>
                    <td style="width: 120px; vertical-align: middle;">
                        <p>Name of the item:</p>
                    </td>
                    <td>
                        <div class="input-field">{{ $product_name }}</div>
                    </td>
                </tr>
            </table>
            <table>
                <tr>
                    <td style="width: 120px; vertical-align: middle;">
                        <p>Manufactured by:</p>
                    </td>
                    <td>
                        <div class="input-field">{{ $manufacturer }}</div>
                    </td>
                </tr>
            </table>
            <table>
                <tr>
                    <td style="width: 120px; vertical-align: middle;">
                        <p>Model No./name:</p>
                    </td>
                    <td>
                        <div class="input-field">{{ $model_no }}</div>
                    </td>
                </tr>
            </table>
            <table>
                <tr>
                    <td style="width: 80px; vertical-align: middle">
                        <p>Serial No.:</p>
                    </td>
                    <td>
                        <div class="input-field">{{ $serial_no }}</div>
                    </td>
                </tr>
            </table>
            <table>
                <tr>
                    <td style="width: 170px; vertical-align: middle;">
                        <p>EMI Loan amount (NPR):</p>
                    </td>
                    <td>
                        <div class="input-field">{{ $emi_amount }}</div>
                    </td>
                </tr>
            </table>
            <table>
                <tr>
                    <td style="width: 120px; vertical-align: middle">
                        <p>Amount in words:</p>
                    </td>
                    <td>
                        <div class="input-field">{{ $amount_in_words }}</div>
                    </td>
                </tr>
            </table>
            <table>
                <tr>
                    <td style="width: 140px;">
                        <p>EMI tenure (months):</p>
                    </td>
                    <td>
                        <div>
                            @foreach ($emi_tenures as $index => $tenure)
                                <div class="cc-box"
                                    style="{{ $index + 1 === strlen($tenure) ? 'border-right: 1px solid #d0a134;' : '' }} @if ($tenure == $selected_tenure) background: #d0a134; color: #fff; @endif">
                                    {{ $tenure }}
                                </div>
                            @endforeach

                            {{-- <div class="cc-box">1</div>
                            <div class="cc-box">4</div>
                            <div class="cc-box">4</div>
                            <div class="cc-box" style="border-right: 1px solid #d0a134;">2</div> --}}
                        </div>
                        {{-- @foreach ($emi_tenures as $tenure)
                            <div style="display: inline-block; margin-right: 12px;">
                                @foreach (str_split($tenure) as $index => $digit)
                                    <div class="cc-box"
                                        @if ($index + 1 === strlen($tenure)) style="border-right: 1px solid #d0a134;" @endif>
                                        {{ $digit }}
                                    </div>
                                @endforeach
                            </div>
                        @endforeach --}}

                    </td>
                </tr>
            </table>

            <p class="declaration">
                I hereby declare that I have read and understood terms and conditions applicable for availing Credit
                Card EMI loan from Siddhartha Bank Ltd. mentioned overleaf and agree to abide by it as applicable.
            </p>

            <table>
                <tr>
                    <td>
                        <div style="text-align:center; width: 150px; height: 40px; line-height: 40px;">
                            {{ $signature_text ?? $card_holder_name ?? 'N/A' }}
                        </div>
                        <div style="border-top: 1px solid #000000; width: 150px;">
                            <p>Signature of Cardholder</p>
                        </div>
                    </td>
                    <td style="width: 220px;">
                        <div style="height: 30px;">

                        </div>
                        <table>
                            <tr>
                                <td style="width: 40px;">Date :</td>
                                <td>
                                    <div style="border-bottom: 1px solid #e39f27;">
                                        <p>{{ $date }}</p>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <div class="section-header">For Merchant's use only</div>
            <div class="sub-box">
                <table>
                    <tr>
                        <td style="width:130px;">Name of merchant:</td>
                        <td>
                            <div class="input-underline">{{ $name_address_merchant }}</div>
                        </td>
                    </tr>
                </table>
                <table>
                    <tr>
                        <td style="width: 140px;padding-top: 20px;">Requested by: Name:</td>
                        <td style="padding-top: 20px;">
                            <div class="input-underline">{{ $requested_by }}</div>
                        </td>
                        <td style="width: 80px;padding-top: 20px;">
                            <div>
                                <p>Signature:</p>
                            </div>
                        </td>
                        <td style="width: 120px;">
                            <div class="input-underline">&nbsp;</div>
                        </td>
                    </tr>
                </table>
                <table>
                    <tr>
                        <td style="width: 80px;padding-top: 20px;">Phone no:</td>
                        <td style="padding-top: 20px;">
                            <div class="input-underline">{{ $merchant_phone }}</div>
                        </td>
                        <td style="width: 80px;padding-top: 20px;">
                            <div>
                                <p>Stamp:</p>
                            </div>
                        </td>
                        <td style="width: 120px;">
                            <div class="">&nbsp;</div>
                        </td>
                    </tr>
                </table>

            </div>

            <div class="section-header">For Bank's use only</div>
            <div class="sub-box">
                <table>
                    <tr>
                        <td style="width: 150px;">Application received by:</td>
                        <td>
                            <div class="input-underline">&nbsp;</div>
                        </td>
                        <td style="width: 40px;">
                            <div>
                                <p>Date:</p>
                            </div>
                        </td>
                        <td style="width: 120px;">
                            <div class="input-underline">&nbsp;</div>
                        </td>
                    </tr>
                </table>
                <table>
                    <tr>
                        <td style="width: 150px;">Available Credit Limit:</td>
                        <td>
                            <div class="input-underline">&nbsp;</div>
                        </td>
                        <td style="width: 170px;">Approved EMI Credit Limit:</td>
                        <td>
                            <div class="input-underline">&nbsp;</div>
                        </td>
                        <td style="width: 40px;">
                            <div>
                                <p>Tenure:</p>
                            </div>
                        </td>
                        <td style="width: 120px;">
                            <div class="input-underline">&nbsp;</div>
                        </td>
                    </tr>
                </table>
                <table>
                    <tr>
                        <td style="width: 30px;">EMI:</td>
                        <td>
                            <div class="input-underline">&nbsp;</div>
                        </td>
                        <td style="width:30px;">From:</td>
                        <td>
                            <div class="input-underline">&nbsp;</div>
                        </td>
                        <td style="width: 30px;">
                            <div>
                                <p>To:</p>
                            </div>
                        </td>
                        <td style="width: 170px;">
                            <div class="input-underline">&nbsp;</div>
                        </td>
                    </tr>
                </table>
                <table>
                    <tr>
                        <td style="width: 100px;">Approved By:</td>
                        <td>
                            <div class="input-underline">&nbsp;</div>
                        </td>

                        <td style="width: 30px;">
                            <div>
                                <p>Date:</p>
                            </div>
                        </td>
                        <td style="width: 170px;">
                            <div class="input-underline">&nbsp;</div>
                        </td>
                    </tr>
                </table>

            </div>
        </div>
    </div>
</body>

</html>
