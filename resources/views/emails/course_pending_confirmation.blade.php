<!DOCTYPE html>
<html lang="th" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:v="urn:schemas-microsoft-com:vml">

<head>
    <title>ขอบคุณที่สนใจเรียนกับ EiMaths-TH</title>
    <meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <link href="https://fonts.googleapis.com/css?family=Noto+Serif" rel="stylesheet" type="text/css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter&family=Work+Sans:wght@700&display=swap" rel="stylesheet"
        type="text/css" />
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
        }

        .container {
            max-width: 700px;
            margin: 0 auto;
            background-color: #f7f7f7;
            padding: 20px;
        }

        .content-box {
            background-color: #ffffff;
            border-radius: 20px;
            border-top: 15px solid orange;
            padding: 30px;
            margin-bottom: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header img {
            height: 150px;
        }

        .title {
            color: #201f42;
            font-family: 'Noto Serif', Georgia, serif;
            font-size: 24px;
            font-weight: 700;
            text-align: center;
            margin-bottom: 20px;
        }

        .info-row {
            display: flex;
            padding: 15px 0;
            border-bottom: 1px solid #efeef4;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            width: 40%;
            font-weight: 600;
            color: #201f42;
        }

        .info-value {
            width: 60%;
            color: #333;
        }

        .highlight-box {
            background-color: #fff8e1;
            border-left: 4px solid orange;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
        }

        .highlight-box h3 {
            color: #e65100;
            margin: 0 0 10px 0;
        }

        .footer {
            text-align: center;
            color: #666;
            font-size: 14px;
            margin-top: 30px;
        }

        .appointment-date {
            font-size: 18px;
            font-weight: bold;
            color: #e65100;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 12px 15px;
            vertical-align: top;
        }

        .info-table tr:nth-child(odd) {
            background-color: #f9f9f9;
        }

        .info-table .label {
            font-weight: 600;
            color: #201f42;
            width: 40%;
        }

        .info-table .value {
            color: #333;
        }
    </style>
</head>

<body style="background-color: #f7f7f7; margin: 0; padding: 0;">
    <table border="0" cellpadding="0" cellspacing="0" role="presentation"
        style="background-color: #f7f7f7; width: 100%;">
        <tbody>
            <tr>
                <td style="padding: 20px;">
                    <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation"
                        style="max-width: 700px; margin: 0 auto;">
                        <tbody>
                            <!-- Logo -->
                            <tr>
                                <td style="text-align: center; padding: 20px 0;">
                                    <img src="{{ $message->embed(public_path('images/logo-eimaths.png')) }}"
                                        alt="EiMaths-TH" style="height: 150px;">
                                </td>
                            </tr>

                            <!-- Main Content -->
                            <tr>
                                <td>
                                    <table border="0" cellpadding="0" cellspacing="0" role="presentation"
                                        style="background-color: #ffffff; border-radius: 20px; border-top: 15px solid orange; width: 100%;">
                                        <tbody>
                                            <tr>
                                                <td style="padding: 30px;">
                                                    <!-- Title -->
                                                    <h1
                                                        style="color: #201f42; font-family: 'Noto Serif', Georgia, serif; font-size: 24px; font-weight: 700; text-align: center; margin: 0 0 20px 0;">
                                                        ขอบคุณที่สนใจเรียนกับ EiMaths-TH
                                                    </h1>

                                                    <p style="color: #333; font-size: 16px; line-height: 1.6; text-align: center; margin-bottom: 30px;">
                                                        เราได้รับข้อมูลการลงทะเบียนของท่านเรียบร้อยแล้ว<br>
                                                        ทีมงานจะติดต่อกลับเพื่อยืนยันนัดหมายในเร็วๆ นี้
                                                    </p>

                                                    <!-- Appointment Highlight -->
                                                    <table border="0" cellpadding="0" cellspacing="0" role="presentation"
                                                        style="background-color: #fff8e1; border-left: 4px solid orange; border-radius: 8px; width: 100%; margin-bottom: 30px;">
                                                        <tbody>
                                                            <tr>
                                                                <td style="padding: 20px;">
                                                                    <h3 style="color: #e65100; margin: 0 0 10px 0; font-size: 18px;">
                                                                        📅 วันนัดหมาย
                                                                    </h3>
                                                                    <p style="color: #e65100; font-size: 20px; font-weight: bold; margin: 0;">
                                                                        {{ \Carbon\Carbon::parse($data['appointment_date'])->locale('th')->translatedFormat('l ที่ j F Y เวลา H:i น.') }}
                                                                    </p>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>

                                                    <!-- Registration Details -->
                                                    <h2 style="color: #201f42; font-size: 18px; margin: 0 0 15px 0; border-bottom: 2px solid #efeef4; padding-bottom: 10px;">
                                                        📋 รายละเอียดการลงทะเบียน
                                                    </h2>

                                                    <table border="0" cellpadding="0" cellspacing="0" role="presentation"
                                                        class="info-table" style="width: 100%; margin-bottom: 20px;">
                                                        <tbody>
                                                            <tr style="background-color: #f9f9f9;">
                                                                <td class="label" style="padding: 12px 15px; font-weight: 600; color: #201f42; width: 40%;">ชื่อผู้ปกครอง</td>
                                                                <td class="value" style="padding: 12px 15px; color: #333;">{{ $data['name'] }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td class="label" style="padding: 12px 15px; font-weight: 600; color: #201f42; width: 40%;">อีเมล</td>
                                                                <td class="value" style="padding: 12px 15px; color: #333;">{{ $data['email'] }}</td>
                                                            </tr>
                                                            <tr style="background-color: #f9f9f9;">
                                                                <td class="label" style="padding: 12px 15px; font-weight: 600; color: #201f42; width: 40%;">เบอร์โทรศัพท์</td>
                                                                <td class="value" style="padding: 12px 15px; color: #333;">{{ $data['telp'] }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td class="label" style="padding: 12px 15px; font-weight: 600; color: #201f42; width: 40%;">ชื่อนักเรียน</td>
                                                                <td class="value" style="padding: 12px 15px; color: #333;">{{ $data['student_name'] }} ({{ $data['student_nickname'] }})</td>
                                                            </tr>
                                                            <tr style="background-color: #f9f9f9;">
                                                                <td class="label" style="padding: 12px 15px; font-weight: 600; color: #201f42; width: 40%;">ระดับชั้น</td>
                                                                <td class="value" style="padding: 12px 15px; color: #333;">{{ $data['grade'] }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td class="label" style="padding: 12px 15px; font-weight: 600; color: #201f42; width: 40%;">คอร์สที่สนใจ</td>
                                                                <td class="value" style="padding: 12px 15px; color: #333;">{{ $data['course_name'] }}</td>
                                                            </tr>
                                                            <tr style="background-color: #f9f9f9;">
                                                                <td class="label" style="padding: 12px 15px; font-weight: 600; color: #201f42; width: 40%;">สาขา</td>
                                                                <td class="value" style="padding: 12px 15px; color: #333;">{{ $data['department_id'] }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td class="label" style="padding: 12px 15px; font-weight: 600; color: #201f42; width: 40%;">วันเรียน</td>
                                                                <td class="value" style="padding: 12px 15px; color: #333;">{{ $data['day'] }}</td>
                                                            </tr>
                                                            <tr style="background-color: #f9f9f9;">
                                                                <td class="label" style="padding: 12px 15px; font-weight: 600; color: #201f42; width: 40%;">เวลาเรียน</td>
                                                                <td class="value" style="padding: 12px 15px; color: #333;">{{ $data['period'] }}</td>
                                                            </tr>
                                                        </tbody>
                                                    </table>

                                                    <!-- Footer Message -->
                                                    <p style="color: #666; font-size: 14px; line-height: 1.6; text-align: center; margin-top: 30px;">
                                                        หากมีข้อสงสัยหรือต้องการเปลี่ยนแปลงนัดหมาย<br>
                                                        กรุณาติดต่อเราผ่านทางเว็บไซต์หรือโทรศัพท์
                                                    </p>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>

                            <!-- Footer -->
                            <tr>
                                <td style="text-align: center; padding: 20px; color: #999; font-size: 12px;">
                                    <p style="margin: 0;">© {{ date('Y') }} EiMaths-TH. All rights reserved.</p>
                                    <p style="margin: 5px 0 0 0;">อีเมลนี้ถูกส่งโดยอัตโนมัติ กรุณาอย่าตอบกลับ</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
</body>

</html>
