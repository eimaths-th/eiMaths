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
                <td colspan="4" align="center" width="75%"><b>
                        {{--  {{ $mes }}<br>
                        {{ $text }} --}}
                    </b></td>

                <td width="25%" align="center" class="aaa"><b>ใบกำกับภาษี/ใบเสร็จรับเงิน <br> TAX
                        INVOICE/RECEIPT</b></td>

            </tr>
        </table>
        <table align="center" width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td width="30%" class="grey">นามลูกค้า,ที่อยู่/Client,Address</td>
                <td width="5%" style="padding-left:10px;" align="right" class="grey">
                </td>
                <td width="5%" style="padding-left:12px;">
                </td>
                <td width="30%" align="right" class="grey">เลขที่ / Document No. </td>
                <td width="30%" style="padding-left:5px;"> <b> {{ $data->doc_number }} </b></td>
            </tr>
            <tr>
                <td>
                    @if(isset($data->doc_number) && $data->doc_number == 'RP2511010')
                        <font class="grey"><b>
                            บริษัท สำนักงานกฎหมาย ธันย์ธรณ์เทพ จำกัด<br>
                            เลขที่ 1055/525 สเตท ทาวเวอร์ ชั้น 28 ถนนสีลม แขวงสีลม เขตบางรัก กรุงเทพฯ 10500<br>
                            <span style="white-space: nowrap;">เลขประจำตัวผู้เสียภาษีอากร : 0105566182291</span><br>
                            
                        </b></font>
                    @else
                        <font class="grey"><b> คุณ {{ $data->parent_name }} / {{ $data->parent_mobile }} </b></font>
                    @endif
                </td>
                <td align="right" style="padding-left:15px;" class="grey">
                </td>
                <td align="left" style="padding-left:12px;"> <b>
                        {{-- {{ $etype }} --}}</b></td>
                <td align="right" class="grey">วันที่ / Date</td>
                <td style="padding-left:5px;"> <b>{{ $date }}</b></td>
            </tr>
            <tr>
                <td>
                    <font class="grey">รหัสลูกค้า/Customer's Code <b> {{ $data->ccode }} </b> </font>
                </td>
                <td align="right" style="padding-left:15px;" class="grey"></td>
                <td align="left" style="padding-left:12px;"> <b>
                        {{-- {{ $etype }} --}}</b></td>
                <td align="right" class="grey"> เงื่อนไขการชำระเงิน / Credit Term</td>
                <td style="padding-left:10px;">.......................... </td>
            </tr>
            <tr>
                <!-- <td colspan="3"><font class="grey">ที่อยู่</font> <b>{{ $data->street_address }} {{ $data->street_address2 }}
                                {{ $data->city }} {{ $data->province }} {{ $data->postcode }} </b></td> -->
                <td colspan="3">ชื่อนักเรียน / Name <b> {{ $data->cname }} </b> </td>
                <td width="45%" align="right" class="grey">ครบกำหนด / Due Date</td>
                <td width="30%" style="padding-left:10px;">..........................</td>
            </tr>
            <tr>
                <td colspan="3"></td>

                <td align="right" width="45%" class="grey"></td>
                <td align="right" width="30%" style="padding-left:10px;"></td>
            </tr>
            <!-- <tr>
                        <td colspan="5"><font class="grey">โทร.</font> <b> {{ $data->mobile }} </b></td>

                    </tr> -->
        </table>
        <div class="row tbr">
        </div>
        <div class="row tbr">
            <table class="centered-table" width="100%" cellpadding="0" cellspacing="0">
                <tr class="active">
                    <td bgcolor="#FF6900" align="center" width="10%" class="whiteh">ลำดับที่<br> NO.</td>
                    <td bgcolor="#FF6900" align="center" class="whiteh">รายการ <br>Description</td>
                    <td bgcolor="#FF6900" align="center" class="whiteh">จำนวน <br>Quantity</td>
                    <td bgcolor="#FF6900" align="center" class="whiteh">หน่วย <br>Unit</td>
                    <td bgcolor="#FF6900" align="center" class="whiteh">ราคาต่อหน่วย <br>Unit Price</td>
                    <td bgcolor="#FF6900" align="center" class="whiteh">ส่วนลด <br>Discount</td>
                    <td bgcolor="#FF6900" align="center" class="whiteh">จำนวนเงิน <br>Amount</td>
                </tr>
                @foreach ($items as $x => $receipt_detail)
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
                @endforeach

                <tr>
                    <td align="center" style="vertical-align: top;"></td>
                    <td align="left"></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
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
                <tr>
                    <td width="37%" class="activetop"
                        style="padding-top:20px;padding-bottom:30px; padding-left:40px;">
                        <table width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td></td>
                                <td colspan="2" style="padding-left:50px;" class="  grey">
                                    การชำระเงิน/Payment{{-- {{ $data->payment }} --}}</td>

                            </tr>
                            <tr>
                                <td class="active activebottom activetop activeleft" width="10%" align="center">
                                    @if ($data->payment == 1)
                                        <img src="{{ asset('images/check.png') }}" alt="..." height="18"
                                            style="padding-bottom: 1px">
                                    @endif
                                </td>
                                <td style="padding-left:30px;" class="  grey">1.เงินสด/Cash</td>
                                <td class="  grey"></td>

                            </tr>
                            <tr>
                                <td class="active_twin activebottom activeleft" width="10%" align="center">
                                    @if ($data->payment == 2)
                                        <img src="{{ asset('images/check.png') }}" alt="..." height="18"
                                            style="padding-bottom: 1px">
                                    @endif
                                </td>

                                <!-- <td class="active activebottom activeleft" width="10%" align="center"><i
                                                style="font-size: 1.8em; color:red" class="fa fa-close"></i></td> -->
                                <td style="padding-left:30px;" class=" grey">2.โอนเงิน/Transfer</td>
                                <td class="  grey"></td>

                            </tr>
                            <tr>
                                <td class="active activebottom activeleft" width="10%" align="center">
                                    @if ($data->payment == 3)
                                        <img src="{{ asset('images/check.png') }}" alt="..." height="18"
                                            style="padding-bottom: 1px">
                                    @endif
                                </td>
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
                                <td width="15%" align="right"> <b> {{ number_format($book_price, 2, '.') }}
                                    </b></td>
                                <td width="8%"></td>


                            </tr>
                            <tr>
                                <td width="10%"></td>
                                <td width="67%" class="grey">มูลค่าที่คำนวนภาษี/Pre-Vat Amount</td>

                                <td width="15%" align="right"> <b>
                                        {{ number_format($vat_price - $vat_val, 2, '.') }}</b></td>
                                <td width="8%"></td>

                            </tr>
                            <tr>
                                <td width="10%"></td>
                                <!-- <td width="20%"></td> -->
                                <td width="67%" class="grey">ส่วนลด/Discout</td>
                                <td width="15%" align="right"><b>{{ number_format($discount_val, 2, '.') }}</b>
                                </td>
                                <td width="8%"></td>

                            </tr>

                            <tr>
                                <td width="10%"></td>
                                <!-- <td width="20%"></td> -->
                                <td width="67%" class="grey">ภาษีมูลค่าเพิ่ม 7% / Vat 7%</td>
                                <td width="15%" align="right"><b>{{ number_format($vat_val, 2, '.') }}</b></td>
                                <td width="8%"></td>

                            </tr>
                            <tr>
                                <td width="10%"></td>
                                <!-- <td width="20%"></td> -->
                                <td width="67%" class="grey">ค่าธรรมเนียมบัตรเครดิต / Service Charge</td>
                                <td width="15%" align="right"><b>{{ number_format($orther_fee, 2, '.') }}</b>
                                </td>
                                <td width="8%"></td>

                            </tr>
                        </table>
                    </td>

                </tr>
                <tr class="active">
                    <td align="center" class="grey">รวมทั้งสิ้น<br>Grand Total</td>
                    <td align="left"></td>
                    <td align="center"><b>{{ $price_text }}</b><br><b>{{ $price_texten }}</b></td>
                    <td align="center"><b>{{ number_format($net_price, 2, '.') }}</b></td>

                    <td align="right" class="grey">บาท<br>Baht</td>
                </tr>
                {{--  <tr class="active">
                    <td width="20%" align="center" class="grey">รวมทั้งสิ้น<br>Grand Total</td>
                    <td colspan="4" width="40%" align="left">
                        <b>{{ $price_text }}</b><br><b>{{ $price_texten }}</b>
                    </td>
                    <td width="20%" align="center"><b>{{ $price_val }}</b></td>

                    <td width="20%" align="center" class="grey">บาท<br>Baht</td>
                </tr>
 --}}
            </table>

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
                    <td align="center">.........................<br>Received by<br>ผู้รับบริการ</td>
                    <td align="center">{!! $file_att !!}</td>
                    <td align="center">.........................<br>ผู้จัดการ<br>Manager</td>
                </tr>
                <tr>
                    <td align="center" class="grey"></td>
                    <td></td>
                    <td align="center" class="grey"></td>
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
                    <td align="center"></td>
                    <td></td>
                    <td align="center"></td>
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
