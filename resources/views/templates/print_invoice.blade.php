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

        /*
        font.grey {
            color: rgb(141, 138, 138);

        } */


        .tbr table tr.active td {
            border-top: 2px solid rgb(138, 175, 61);
            border-bottom: 2px solid rgb(138, 175, 61);
        }

        .tbr table td.activetop {
            border-top: 2px solid rgb(138, 175, 61);
        }

        .tbr table td.active_twin {
            border-left: 2px solid rgb(138, 175, 61);
            border-right: 2px solid rgb(138, 175, 61);
        }

        .tbr table td.active {

            border-right: 2px solid rgb(138, 175, 61);
        }

        .tbr table td.activebottom {
            border-bottom: 2px solid rgb(138, 175, 61);
        }

        .tbr table td.activeright {
            border-right: 2px solid rgb(138, 175, 61);
        }

        .tbr table td.activeleft {
            border-left: 2px solid rgb(138, 175, 61);
        }

        .aaa {
            color: rgb(138, 175, 61);
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
    </style>
</head>

<body>
    <div class="row" id="reportPrinting" style="width: 90%;margin: 0 auto;">

        <table align="center" width="99%" cellpadding="0" cellspacing="0">

            <tr>
                <td width="25%" rowspan="6" align="center"><img src="{{ asset('images/logo.png') }}"
                        alt="..." height="120" style="padding-bottom: 5px; display: block; margin: 0 auto;"></td>
                <td align="right">
                    <table align="right">
                        <tr>
                            <td align="right" style="vertical-align: top;" height="5px">eiMaths Ratchaphruek
                            </td>
                        </tr>
                        <tr>
                            <td align="right" style="vertical-align: top;"height="5px">The Crystal SB Ratchaphruek,
                                3rd floor<br>
                                555/9 Bang Kruai District, Nonthaburi 11130
                            </td>
                        </tr>
                        <tr>
                            <td align="right" style="vertical-align: top;"height="5px">Tax: 0125563031038</td>
                        </tr>
                        <tr>
                            <td align="right" style="vertical-align: top;" height="5px">Phone: 0 616-208-666</td>
                        </tr>
                        <tr>
                            <td align="right" style="vertical-align: top;" height="50px"></td>
                        </tr>
                    </table>
                </td>

            </tr>


        </table>


        <table align="center" width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td colspan="4" align="center"><b>
                        {{--  {{ $mes }}<br>
                        {{ $text }} --}}
                    </b></td>

                <td align="center" class="aaa"><b>ต้นฉบับ <br> Original <br>สำหรับลูกค้า</b></td>

            </tr>
            <tr>
                <td width="25%" class="grey">นามลูกค้า,เบอร์โทร/Client,Telephone</td>
                <td width="25%" style="padding-left:10px;" align="right" class="grey">
                    รหัสลูกค้า/Customer's Code</td>
                <td width="25%" style="padding-left:12px;"> <b> {{ $data->ccode }} </b>
                </td>
                <td width="10%" align="right" class="grey">Receipt No.</td>
                <td width="15%" style="padding-left:5px;"> <b> {{ $data->doc_number }} </b></td>
            </tr>
            <tr>
                <td>
                    <font class="grey">{{ $ctype }}</font> <b> {{ $data->cname }} / {{ $data->ctelephone }}
                    </b>
                </td>
                <td align="right" style="padding-left:15px;" class="grey">ประเภทค่าใช้จ่าย</td>
                <td align="left" style="padding-left:12px;"> <b>
                        {{ $etype }}</b></td>
                <td align="right" class="grey">วันที่ / Date</td>
                <td style="padding-left:5px;"> <b>{{ $date }}</b></td>
            </tr>
            {{--   <tr>
                        <!-- <td colspan="3"><font class="grey">ที่อยู่</font> <b>{{ $data->street_address }} {{ $data->street_address2 }}
                                {{ $data->city }} {{ $data->province }} {{ $data->postcode }} </b></td> -->
                        <td colspan="3"></td>
                        <td width="45%" align="right" class="grey">เงื่อนไขการชำระเงิน / Credit Term</td>
                        <td width="30%" style="padding-left:10px;">..........................</td>
                    </tr>
                    <tr>
                        <td colspan="3"></td>

                        <td align="right" width="45%" class="grey">ครบกำหนด / Due Date</td>
                        <td align="right" width="30%" style="padding-left:10px;">..........................</td>
                    </tr> --}}
            <!-- <tr>
                        <td colspan="5"><font class="grey">โทร.</font> <b> {{ $data->mobile }} </b></td>

                    </tr> -->
        </table>
        <div class="row tbr">
            <table class="centered-table" width="100%" cellpadding="0" cellspacing="0">
                <tr class="active">
                    <td align="center" width="10%" class="grey">ลำดับที่<br> NO.</td>
                    <td align="center" class="grey">รายการ <br>Description</td>
                    <td align="center" class="grey">จำนวน <br>Quantity</td>
                    {{--  <td align="center" class="grey">หน่วย <br>Unit</td> --}}
                    <td align="center" class="grey">ราคาต่อหน่วย <br>Unit Price</td>
                    {{-- <td align="center" class="grey">ส่วนลด <br>Discount</td> --}}
                    <td align="center" class="grey">จำนวนเงิน <br>Amount</td>

                </tr>
                @php
                    $total = 0; // Initialize the total variable
                @endphp
                @foreach ($items as $x => $receipt_detail)
                    <tr>
                        <td align="center" style="vertical-align: top;">{{ (int) $x + 1 }}</td>
                        <td align="left"> Class Level {!! $receipt_detail['pname'] !!}</td>
                        <td align="center">{{ $receipt_detail['quantity'] }}</td>
                        {{-- <td align="center">{{ $receipt_detail['unit'] }}</td> --}}
                        <td align="right" style="padding-right:20px;">{{ $receipt_detail['price'] }}</td>
                        {{-- <td align="center">{{ $receipt_detail['discount'] }}</td> --}}
                        <td align="right" style="vertical-align: top; padding-right:20px;">
                            {{ number_format($receipt_detail['quantity'] * $receipt_detail['price'], 2, '.') }}</td>
                    </tr>
                    @php
                        $total += $receipt_detail['quantity'] * $receipt_detail['price']; // Accumulate the total
                    @endphp
                @endforeach
                {{--  <tr>
                        <td align="center" style="vertical-align: top;">1</td>
                        <td align="left">{!! $des_text !!}</td>
                        <td ></td>
                        <td ></td>
                        <td ></td>
                        <td ></td>
                        <td align="center" style="vertical-align: top;">{{ $price_val }}</td>
                    </tr> --}}
                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
                @php
                    $total = $total + ($data->refund + $data->register_fee - $data->promotion);
                @endphp
                <tr class="active">
                    <td align="center" class="grey">รวมทั้งสิ้น<br>Grand Total</td>
                    <td align="left"></td>
                    <td align="center"><b>{{ $price_text }}</b><br><b>{{ $price_texten }}</b></td>
                    <td align="center"><b>{{ number_format($total, 2, '.') }}</b></td>

                    <td align="right" class="grey">บาท<br>Baht</td>
                </tr>
            </table>
            {{-- <table align="center" width="99%" cellpadding="0" cellspacing="0">
                        <tr>
                            <td width="37%" class="activetop"
                                style="padding-top:20px;padding-bottom:30px; padding-left:40px;">
                                <table width="100%" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td></td>
                                        <td colspan="2" style="padding-left:50px;" class="  grey">
                                            การชำระเงิน/Payment</td>

                                    </tr>
                                    <tr>
                                        <td class="active activebottom activetop activeleft" width="10%"></td>
                                        <td style="padding-left:30px;" class="  grey">1.เงินสด/Cash</td>
                                        <td class="  grey"></td>

                                    </tr>
                                    <tr>
                                        <td class="active_twin activebottom activeleft" width="10%"
                                            align="center"><img src="{{ asset('images/close.png') }}" alt="..."
                                                height="14" style="padding-bottom: 1px"></td>

                                        <!-- <td class="active activebottom activeleft" width="10%" align="center"><i
                                                style="font-size: 1.8em; color:red" class="fa fa-close"></i></td> -->
                                        <td style="padding-left:30px;" class=" grey">2.โอนเงิน/Transfer</td>
                                        <td class="  grey"></td>

                                    </tr>
                                    <tr>
                                        <td class="active activebottom activeleft" width="10%"></td>
                                        <td style="padding-left:30px;" class=" grey">3.บัตรเครดิต/Credit Card</td>
                                        <td class="  grey"></td>

                                    </tr>
                                </table>
                            </td>
                            <td colspan="6" class="activetop activeleft">
                                <table width="100%" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td width="10%"></td>
                                        <td width="67%" class="grey">มูลค่าที่ยกเว้นภาษี/Non-Vat Amount</td>
                                        <!-- <td width="20%"></td> -->
                                        <td width="15%" align="right"> <b> 00.00 </b></td>
                                        <td width="8%"></td>


                                    </tr>
                                    <tr>
                                        <td width="10%"></td>
                                        <td width="67%" class="grey">มูลค่าที่คำนวนภาษี/Pre-Vat Amount</td>

                                        <td width="15%" align="right"> <b>
                                                {{ $vat_price_val }} </b></td>
                                        <td width="8%"></td>

                                    </tr>
                                    <tr>
                                        <td width="10%"></td>
                                        <!-- <td width="20%"></td> -->
                                        <td width="67%" class="grey">ส่วนลด/Discout</td>
                                        <td width="15%" align="right"><b>{{ $discount_val }}</b></td>
                                        <td width="8%"></td>

                                    </tr>
                                    <tr>
                                        <td width="10%"></td>
                                        <!-- <td width="20%"></td> -->
                                        <td width="67%" class="grey">ภาษีมูลค่าเพิ่ม 7% / Vat 7%</td>
                                        <td width="15%" align="right"><b>{{ $vat_val }}</b></td>
                                        <td width="8%"></td>

                                    </tr>
                                </table>
                            </td>

                        </tr>
                        <tr class="active">
                            <td width="20%" align="center" class="grey">รวมทั้งสิ้น<br>Grand Total</td>
                            <td colspan="4" width="40%" align="left">
                                <b>{{ $price_text }}</b><br><b>{{ $price_texten }}</b>
                            </td>
                            <td width="20%" align="center"><b>{{ $price_val }}</b></td>

                            <td width="20%" align="center" class="grey">บาท<br>Baht</td>
                        </tr>

                    </table> --}}

            <!-- <table align="center" width="85%" cellpadding="0" cellspacing="0">
                        <tr>
                            <td align="center" class="grey">ได้รับริการตามบริการถูกต้องแล้ว</td>
                            <td align="center" class="grey">ในนามบริษัท อินฟินิท เบรน จำกัด</td>
                        </tr>
                    </table> -->

            <table align="center" width="100%" cellpadding="0" cellspacing="0" style="margin-top:30px;">
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td align="center">.........................</td>
                    <td align="center">.........................</td>
                </tr>
                <tr>
                    <td align="center" class="grey">ผู้รับ</td>
                    <td align="center" class="grey">ผู้จัดการ</td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td align="center">Received by</td>
                    <td align="center">Manager</td>
                </tr>

            </table>

        </div>
        <div class="row tbr">

        </div>



</body>
