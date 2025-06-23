<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!-- Meta, title, CSS, favicons, etc. -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link
        href="https://fonts.googleapis.com/css2?family=Sarabun:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800&display=swap"
        rel="stylesheet">
    <link href='https://fonts.googleapis.com/css?family=Roboto' rel='stylesheet'>
    <style>
        /*  table {
            font-size: 0.7rem;
        } */
        @font-face {
            font-family: 'THSarabunNew';
            font-size: 18px;
            font-style: normal;
            font-weight: normal;
            src: url("{{ asset('fonts/THSarabunNew.ttf') }}") format("truetype");

        }

        @font-face {
            font-family: 'THSarabunNew Bold';
            font-size: 18px;
            font-style: normal;
            font-weight: bold;
            src: url("{{ asset('fonts/THSarabunNew Bold') }}") format("truetype");
        }


        body {
            font-family: 'sarabun', 'Roboto';
            font-size: 16px;
        }

        table td.grey {
            color: rgb(141, 138, 138);

        }

        table td.whiteh {
            color: rgb(255, 255, 255);

        }

        /*
        font.grey {
            color: rgb(141, 138, 138);

        } */


        .tbr table tr.active td {
            border-top: 2px solid rgb(255, 105, 0);
            border-bottom: 2px solid rgb(255, 105, 0);
        }

        .tbr table td.activetop {
            border-top: 2px solid rgb(255, 105, 0);
        }

        .tbr table td.active_twin {
            border-left: 2px solid rgb(255, 105, 0);
            border-right: 2px solid rgb(255, 105, 0);
        }

        .tbr table td.active {

            border-right: 2px solid rgb(255, 105, 0);
        }

        .tbr table td.activebottom {
            border-bottom: 2px solid rgb(255, 105, 0);
        }

        .tbr table td.activeright {
            border-right: 2px solid rgb(255, 105, 0);
        }

        .tbr table td.activeleft {
            border-left: 2px solid rgb(255, 105, 0);
        }

        .aaa {
            color: rgb(255, 105, 0);
        }

        div.row.tbr {
            /* width: 80%; */
            width: 100%;
            margin: 0 auto;
            margin-bottom: 20px;
        }

        table.centered-table {
            width: 100%;
            margin: 0 auto;
            /* This centers the table inside the div */
        }

        .bottom-table {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            background-color: white;
            /* Add background color if needed */
        }
    </style>
</head>

<body>
    <div class="row" id="reportPrinting" style="width: 90%;margin: 0 auto;">

        <table align="center" width="99%" cellpadding="0" cellspacing="0">

            <tr>
                <td width="15%" align="center" style="margin: 0 auto;"><img src="{{ asset('images/logo_meta.png') }}"
                        alt="..." height="110" style="padding-top: 5px; display: block; margin: 0 auto;"></td>
                <td align="left">
                    <table align="left">
                        <tr>
                            <td align="left" style="vertical-align: top;" height="5px">บริษัท เมต้า โนวเลจ จำกัด
                            </td>
                        </tr>
                        <tr>
                            <td align="left" style="vertical-align: top;"height="5px">555 / 9 หมู่ 1 ตำบลบางขนุน
                                อำเภอบางกรวย จังหวัดนนทบุรี 11130
                            </td>
                        </tr>
                        <tr>
                            <td align="left" style="vertical-align: top;"height="5px">เลขประจำตัวผู้เสียภาษี :
                                0125565026368</td>
                        </tr>
                        <tr>
                            <td align="left" style="vertical-align: top;" height="5px">โทร. 061 620 8666</td>
                        </tr>
                    </table>
                </td>
                <td align="right">
                    <img src="{{ asset('images/logo.png') }}" alt="..." height="80"
                        style="padding-bottom: 5px; display: block; margin: 0 auto;">
                </td>

            </tr>


        </table>

        <table align="center" width="100%" cellpadding="0" cellspacing="0">
            <tr>
                {{-- <td colspan="2" align="center" width="75%"><b>
                        
                    </b></td> --}}

                <td width="25%" align="center" class="aaa"><b>ใบสำคัญจ่าย <br> Payment Voucher </b></td>

            </tr>
        </table>

        <table align="center" width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td width="25%" class="grey">จ่ายให้/Pay to</td>
                <td width="20%" style="padding-left:5px;"> <b> {{ $withdraw->user->name }} </b>
                </td>
                <td width="15%" style="padding-left:12px;"> </td>
                <td width="20%" align="right" class="grey">เลขที่ / Voucher No. </td>
                <td width="20%" style="padding-left:10px;">
                    <b>
                        {{-- Voucher Number  --}}
                        {{-- i want to PV2025040001 --}}
                        {{ $withdraw->pv_number }}
                    </b>
                </td>
            </tr>
            <tr>
                <td>
                    <font class="grey"><b> ช่องทางชำระ / Payment Method
                        </b> </font>
                </td>
                <td style="padding-left:5px;">
                    <b>
                        @php
                            $method = $withdraw->payment_type;
                            $text = ' ';
                            if ($method == 1) {
                                # code...
                                $text = 'PromptPay';
                            } elseif ($method == 2) {
                                # code...
                                $text = 'Bank Account';
                            } else {
                                $text = 'ไม่พบข้อมูล';
                            }
                        @endphp
                        {{ $text }}
                    </b>
                </td>
                <td align="left" style="padding-left:12px;"> <b>
                        {{-- {{ $etype }} --}}</b></td>
                <td align="right" class="grey">วันที่ / Date</td>
                <td style="padding-left:10px;"> <b>{{ date('d-m-Y') }}</b></td>
            </tr>
            <tr>
                <td>
                    <font class="grey"><b> เลขที่บัญชี / Account number
                        </b> </font>
                </td>
                <td style="padding-left:5px;"> <b> {{ $account }} </b> </td>
                <td align="left" style="padding-left:12px;"> <b>
                        {{-- {{ $etype }} --}}</b></td>
                <td align="right" class="grey"></td>
                <td style="padding-left:10px;"></b></td>
            </tr>
            <tr>
                <td colspan="3"></td>

                <td align="right" width="30%" class="grey"></td>
                <td align="right" width="30%" style="padding-left:10px;"></td>
            </tr>
        </table>
        <div class="row tbr">
        </div>
        <div class="row tbr">
            <table class="centered-table" width="100%" cellpadding="0" cellspacing="0">
                <tr class="active">
                    <td bgcolor="#FF6900" align="center" width="10%" class="whiteh">ลำดับที่<br> NO.</td>
                    <td bgcolor="#FF6900" align="center" width="60%" class="whiteh">รายการ <br>Description</td>
                    <td bgcolor="#FF6900" align="center" width="10%" class="whiteh"></td>
                    {{-- <td bgcolor="#FF6900" align="center" width="10%" class="whiteh"></td>
                    <td bgcolor="#FF6900" align="center" width="10%" class="whiteh"></td> --}}
                    <td bgcolor="#FF6900" align="center" width="30%" class="whiteh">จำนวนเงิน <br>Amount</td>
                </tr>
                <tr>
                    <td align="center" style="vertical-align: top;"> 1 </td>
                    <td align="center"> ถอนเงินค่า Commission </td>
                    <td></td>
                    <td align="center"> {{ number_format($withdraw->withdraw_amount, 2) }} </td>

                </tr>
                {{-- @foreach ($items as $x => $receipt_detail)
                    <tr>
                        <td align="center" style="vertical-align: top;">{{ (int) $x + 1 }}</td>
                        <td align="center"> {!! $receipt_detail['des'] !!}</td>
                        <td align="center">{{ $receipt_detail['quantity'] }}</td>
                        <td align="center">{{ $receipt_detail['unit'] }}</td>
                        <td align="center" style="padding-right:20px;">
                            {{ number_format($receipt_detail['price'], 2, '.') }}</td>
                        <td align="center">{{ $receipt_detail['discount'] }}</td>
                        <td align="center" style="vertical-align: top; padding-right:20px;">
                            {{ number_format($receipt_detail['price'] * $receipt_detail['quantity'] - $receipt_detail['discount'], 2, '.') }}
                        </td>
                    </tr>
                @endforeach --}}

                <tr>
                    <td align="center" style="vertical-align: top;"></td>
                    <td align="left"></td>

                    <td align="center" style="vertical-align: top; padding-right:20px;">
                        {{-- {{ number_format($total, 2, '.') }} --}}</td>
                </tr>
                {{-- <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr> --}}
                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>

            </table>
            <table align="center" width="99%" cellpadding="0" cellspacing="0">
                {{-- <tr>
                    <td width="37%" class="activetop"
                        style="padding-top:20px;padding-bottom:30px; padding-left:40px;">
                        <table width="100%" cellpadding="0" cellspacing="0">

                        </table>
                    </td>
                    <td colspan="6" class="activetop activeleft">
                        <table width="100%" cellpadding="0" cellspacing="0">

                        </table>
                    </td>

                </tr> --}}
                <tr class="active">
                    <td align="center" class="grey" style="padding-bottom:5px; padding-top:5px;">
                        รวมทั้งสิ้น<br>Grand Total</td>
                    <td align="left"></td>
                    <td align="center"></td>
                    <td align="center"><b>{{ number_format($withdraw->withdraw_amount, 2) }}</b></td>

                    <td align="right" class="grey">บาท<br>Baht</td>
                </tr>

            </table>

            <table align="center" width="100%" cellpadding="0" cellspacing="0" style="margin-top:50px;">
                <tr>
                    <td width="33%" align="center" style="padding-bottom:30px;">
                        .........................<br>ผู้อนุมัติ
                    </td>
                    <td width="33%" align="center" style="padding-bottom:30px;">
                        .........................<br>ผู้จัดทำ
                    </td>
                    <td width="33%" align="center" style="padding-bottom:30px;">
                        .........................<br>ผู้รับเงิน
                    </td>
                </tr>
                <tr>
                    <td align="center">เงินสดจำนวน .................บาท</td>
                    <td align="center">เช็คจำนวน .................บาท</td>
                    <td></td>
                </tr>
            </table>


        </div>
        <div class="row tbr">

        </div>
        {{--  <div style="position: relative;"> --}}
        <!-- Your table with a class for styling -->
        <table class="print-table" width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td align="center"><img src="{{ asset('images/banner.png') }}" alt="..."
                        style="padding-bottom: 5px; display: block; margin: 0 auto;"></td>
            </tr>
        </table>
        {{-- </div> --}}
</body>
