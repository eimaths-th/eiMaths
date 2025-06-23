@extends('affiliate.frontend.main')
@section('style')
@endsection
@section('content')
    @if (request('ref'))
        @php
            $ref = request('ref');
            // dd(request('ref'), $ref);
        @endphp
    @else
        @php
            $ref = '';
            // dd(request('ref'), $ref);
        @endphp
    @endif
    <div id="content-area">
        <div class="full-page" data-type="component-text" data-preview="" data-aoraeditor-title="All Section"
            data-aoraeditor-categories="Affiliate Page">
            <div class="row">
                <div class="col-sm-12 ui-resizable" data-type="container-content">
                    <div data-aoraeditor-categories="Affiliate Page" data-aoraeditor-title="Affiliate Title"
                        data-preview="{{ asset('frontend/infixlmstheme/img/snippets/preview/affiliate/title.png') }}"
                        data-type="component-text">
                        <div class="affiliate_bradcam_area">
                            <div class="container">
                                <div class="row">
                                    <div class="col-lg-10 offset-lg-1">
                                        <div class="breadcam_text text-center"><br><span><span
                                                    style="font-size:28px;">เข้าร่วมโปรแกรม Affiliate
                                                    ของเรา</span></span>
                                            <h4><strong>เป็นส่วนหนึ่งของความสำเร็จของเรา รับค่าคอมมิชชั่นสูงสุด 15%
                                                    จากการแนะนำ</strong></h4>
                                            <p>เรามีค่าคอมมิชชั่นที่น่าสนใจสำหรับแต่ละการแนะนำที่ประสบความสำเร็จ</p>
                                            <a class="theme_btn" href="{{ route('affiliate.registration') }}">
                                                {{-- <a class="theme_btn" href="https://ecommerce.eimaths-th.com/affiliate/registration"> --}}
                                                เข้าร่วมเป็นครอบครัว Affiliate</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12 ui-resizable" data-type="container-content">
                    <div data-aoraeditor-categories="Affiliate Page" data-aoraeditor-title="Affiliate Commission"
                        data-preview="{{ asset('frontend/infixlmstheme/img/snippets/preview/affiliate/commission_section.png') }}"
                        {{-- data-preview="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/img/snippets/preview/affiliate/commission_section.png" --}} data-type="component-text">
                        <div class="lms_section">
                            <div class="container">
                                <div class="row justify-content-center">
                                    <div class="col-xl-10">
                                        <div class="commision_box">
                                            <div alt=""
                                                class="{{ asset('frontend/infixlmstheme/img/snippets/preview/affiliate/round.svg') }}">
                                                {{-- class="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/img/snippets/preview/affiliate/round.svg"> --}}
                                                &nbsp;</div>
                                            <div class="commision_left">
                                                <h4>อัตราค่าคอมมิชชั่น</h4>
                                                <p class="rate_text">ช่วยกำหนดอนาคตของการค้า</p>
                                                <h3>15%</h3>
                                                <p class="Sale_text">For the First Sale</p><a class="theme_btn small_btn2"
                                                    {{-- href="https://ecommerce.eimaths-th.com/my-affiliate"> --}} href="{{ route('affiliate.login') }}">
                                                    &nbsp;สร้าง
                                                    Affiliate ลิงค์</a>
                                                <p class="discription_text">เสนอค่าคอมมิชชั่น 15%
                                                    สำหรับการขาย
                                                    หากผู้เยี่ยมชมของคุณซื้อสินค้าของเรา
                                                    คุณจะได้รับค่าคอมมิชชั่น สูงสุด15%</p>
                                            </div>
                                            <div class="commision_right flex-fill">
                                                <div class="commision_payment_lists">
                                                    <div class="commision_payment_list">
                                                        <div class="thumb"><img alt=""
                                                                src="{{ asset('frontend/infixlmstheme/img/snippets/preview/affiliate/wallet_1.svg') }}">
                                                            {{-- src="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/img/snippets/preview/affiliate/wallet_1.svg"> --}}
                                                        </div>
                                                        <div class="comission_payment_text">
                                                            <h4>วิธีและกำหนดการจ่ายเงิน</h4>
                                                            <p>การจ่ายค่าคอมมิชชั่นจะดำเนินการทุกเดือนผ่านบัญชีธนาคาร
                                                                ตามที่คุณเลือก</p>
                                                        </div>
                                                    </div>
                                                    <div class="commision_payment_list">
                                                        <div class="thumb"><img alt=""
                                                                src="{{ asset('frontend/infixlmstheme/img/snippets/preview/affiliate/wallet_2.svg') }}">
                                                            {{-- src="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/img/snippets/preview/affiliate/wallet_2.svg"> --}}
                                                        </div>
                                                        <div class="comission_payment_text">
                                                            <h4>อัตราค่าคอมมิชชั่น</h4>
                                                            <p>คุณจะได้รับค่าคอมมิชชั่นตามอัตราที่กำหนด
                                                                โดยขึ้นอยู่กับประเภทของผลิตภัณฑ์หรือบริการที่แนะนำ
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="commision_payment_list">
                                                        <div class="thumb"><img alt=""
                                                                src="{{ asset('frontend/infixlmstheme/img/snippets/preview/affiliate/wallet_3.svg') }}">
                                                            {{-- src="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/img/snippets/preview/affiliate/wallet_3.svg"> --}}
                                                        </div>
                                                        <div class="comission_payment_text">
                                                            <h4>กำหนดเวลาการจ่ายเงิน</h4>
                                                            <p>ค่าคอมมิชชั่นจะถูกจ่ายภายใน 30
                                                                วันหลังจากสิ้นสุดรอบบัญชีหรือยอดเงินถึงต่ำที่กำหนด
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <style type="text/css" data-type="component-text">
                        .about .mt-40 {
                            margin-top: 35px;
                        }

                        @media only screen and (max-width: 767px) {
                            .about .mt-40 {
                                margin-top: 0
                            }
                        }

                        .counter {
                            padding: 0 32px
                        }

                        @media only screen and (min-width: 992px) and (max-width: 1279px) {
                            .counter {
                                padding: 0
                            }
                        }

                        @media only screen and (max-width: 991px) {
                            .counter {
                                padding: 0
                            }
                        }

                        .counter #counters {
                            position: relative;
                            z-index: 1
                        }

                        .counter #counters .row {
                            margin-top: -36px
                        }

                        @media (min-width: 480px) {
                            .counter #counters .col-sm-6 {
                                width: 50%
                            }
                        }

                        .counter-shape {
                            position: absolute;
                            top: 50%;
                            left: 50%;
                            transform: translate(-50%, -50%);
                            --width: 255px;
                            width: var(--width);
                            height: var(--width);
                            border-radius: 100%;
                            background-color: var(--system_primery_color)
                        }

                        @media only screen and (max-width: 767px) {
                            .counter-shape {
                                --width: 175px
                            }
                        }

                        .counter-shape::before,
                        .counter-shape::after {
                            content: "";
                            width: 100%;
                            height: 100%;
                            border-radius: 100%;
                            border: 1px dashed var(--system_primery_color);
                            position: absolute;
                            top: 0;
                            left: 0;
                            opacity: .3;
                        }

                        html[dir=rtl] .counter-shape::before,
                        html[dir=rtl] .counter-shape::after {
                            left: auto;
                            right: 0;
                        }

                        .counter-shape::before {
                            transform: scale(1.3)
                        }

                        .counter-shape::after {
                            transform: scale(1.75)
                        }

                        .counter-item {
                            padding: 40px;
                            border-radius: 24px;
                            position: relative;
                            z-index: 1;
                            background-color: #fff;
                            margin-top: 36px;
                            max-width: 270px;
                        }

                        @media only screen and (min-width: 1280px) and (max-width: 1439px) {
                            .counter-item {
                                padding: 30px
                            }
                        }

                        @media only screen and (min-width: 992px) and (max-width: 1279px) {
                            .counter-item {
                                padding: 30px
                            }
                        }

                        @media only screen and (max-width: 767px) {
                            .counter-item {
                                margin-top: 24px !important;
                                padding: 24px;
                            }
                        }

                        @media only screen and (max-width: 479px) {
                            .counter-item {
                                max-width: 100%;
                            }
                        }

                        .counter-item-icon {
                            --icon: 80px;
                            width: var(--icon);
                            height: var(--icon);
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            border-radius: 100%;
                            margin-bottom: 14px
                        }

                        @media only screen and (min-width: 1280px) and (max-width: 1439px) {
                            .counter-item-icon {
                                margin-bottom: 10px
                            }
                        }

                        @media only screen and (min-width: 992px) and (max-width: 1279px) {
                            .counter-item-icon {
                                --icon: 70px
                            }
                        }

                        @media only screen and (max-width: 767px) {
                            .counter-item-icon {
                                --icon: 60px
                            }
                        }

                        .counter-item-icon>* {
                            width: 36px
                        }

                        @media only screen and (max-width: 767px) {
                            .counter-item-icon>* {
                                width: 24px
                            }
                        }

                        .counter-item label {
                            display: inline-flex
                        }

                        .counter-item h4 {
                            font-size: 32px;
                            line-height: 1.25;
                            line-height: 1;
                            margin-bottom: 5px
                        }

                        @media only screen and (min-width: 992px) and (max-width: 1279px) {
                            .counter-item h4 {
                                font-size: 28px
                            }
                        }

                        @media only screen and (min-width: 768px) and (max-width: 991px) {
                            .counter-item h4 {
                                font-size: 26px
                            }
                        }

                        @media only screen and (max-width: 767px) {
                            .counter-item h4 {
                                font-size: 24px
                            }
                        }

                        @media only screen and (min-width: 1280px) and (max-width: 1439px) {
                            .counter-item h4 {
                                font-size: 21px
                            }
                        }

                        .counter-item p {
                            font-size: 20px;
                            line-height: 1.5;
                            color: currentColor;
                        }

                        @media only screen and (min-width: 992px) and (max-width: 1279px) {
                            .counter-item p {
                                font-size: 18px
                            }
                        }

                        @media only screen and (max-width: 991px) {
                            .counter-item p {
                                font-size: 18px
                            }
                        }

                        .about-content {
                            position: relative;
                            padding-left: 50px
                        }

                        html[dir=rtl] .about-content {
                            padding-left: 0;
                            padding-right: 50px;
                        }

                        @media only screen and (min-width: 992px) and (max-width: 1279px) {
                            .about-content {
                                padding-left: 20px
                            }

                            html[dir=rtl] .about-content {
                                padding-left: 0;
                                padding-right: 20px;
                            }
                        }

                        @media only screen and (max-width: 991px) {
                            .about-content {
                                margin-top: 40px;
                                padding-bottom: 20px;
                                padding-left: 0
                            }

                            html[dir=rtl] .about-content {
                                padding-right: 0;
                            }
                        }

                        @media only screen and (max-width: 767px) {
                            .about-content {
                                margin-top: 26px
                            }
                        }

                        .about-content::after {
                            content: "";
                            width: 100%;
                            height: 100%;
                            border-radius: 100%;
                            background-color: rgba(255, 255, 255, 0.2);
                            position: absolute;
                            top: 0;
                            left: 0;
                            z-index: -1;
                            filter: blur(150px)
                        }

                        html[dir=rtl] .about-content::after {
                            left: auto;
                            right: 0;
                        }

                        .about-content h3 {
                            font-size: 48px;
                            line-height: 1.25;
                            font-weight: 600;
                            margin-bottom: 24px
                        }

                        @media only screen and (min-width: 1280px) and (max-width: 1439px) {
                            .about-content h3 {
                                font-size: 42px
                            }
                        }

                        @media only screen and (min-width: 992px) and (max-width: 1279px) {
                            .about-content h3 {
                                font-size: 36px
                            }
                        }

                        @media only screen and (min-width: 768px) and (max-width: 991px) {
                            .about-content h3 {
                                font-size: 32px
                            }
                        }

                        @media only screen and (max-width: 767px) {
                            .about-content h3 {
                                font-size: 28px
                            }
                        }

                        @media only screen and (min-width: 992px) and (max-width: 1279px) {
                            .about-content h3 {
                                margin-bottom: 18px
                            }
                        }

                        @media only screen and (min-width: 768px) and (max-width: 991px) {
                            .about-content h3 {
                                font-size: 28px
                            }
                        }

                        @media only screen and (max-width: 991px) {
                            .about-content h3 {
                                margin-bottom: 16px
                            }
                        }

                        .about-content p {
                            color: var(--system_paragraph_color);
                            line-height: 1.5;
                            margin-bottom: 26px
                        }

                        @media only screen and (min-width: 992px) and (max-width: 1279px) {
                            .about-content p {
                                margin-bottom: 16px
                            }
                        }

                        @media only screen and (max-width: 991px) {
                            .about-content p {
                                margin-bottom: 14px
                            }
                        }

                        @media only screen and (max-width: 479px) {
                            .about-content p {
                                font-size: 14px;
                                line-height: 1.683
                            }
                        }

                        .about-content ul {
                            width: 56%
                        }

                        @media only screen and (min-width: 1280px) and (max-width: 1439px) {
                            .about-content ul {
                                width: 66%
                            }
                        }

                        @media only screen and (min-width: 992px) and (max-width: 1279px) {
                            .about-content ul {
                                width: 66%
                            }
                        }

                        @media only screen and (max-width: 991px) {
                            .about-content ul {
                                width: 100%;
                                margin-top: 20px
                            }
                        }

                        .about-content ul li {
                            color: var(--system_paragraph_color);
                            --width: 24px
                        }

                        .about-content ul li:not(:last-child) {
                            margin-bottom: 20px
                        }

                        .about-content ul li .icon {
                            color: #38485C;
                            width: var(--width);
                            height: var(--width);
                            flex: 0 0 auto;
                            position: relative;
                            top: 3px
                        }

                        .about-content ul li .icon img,
                        .about-content ul li .icon>* {
                            width: 100%;
                            height: 100%;
                            object-fit: contain;
                            position: absolute;
                            top: 0;
                            left: 0;
                            z-index: -1
                        }

                        html[dir=rtl] .about-content ul li .icon img,
                        html[dir=rtl] .about-content ul li .icon>* {
                            left: auto;
                            right: 0;
                        }

                        .about-content ul li .icon svg path {
                            fill: #CEE8FF;
                            stroke: var(--system_primery_color)
                        }

                        .about-content ul li .content {
                            width: calc(100% - var(--width));
                            flex: 0 0 auto;
                            padding-left: 18px
                        }

                        html[dir=rtl] .about-content ul li .content {
                            padding-left: 0;
                            padding-right: 18px;
                        }

                        .about-content a {
                            --btn-padding-y: 9px;
                            min-width: 120px
                        }

                        @media only screen and (min-width: 992px) and (max-width: 1279px) {
                            .about-content a {
                                margin-top: 10px
                            }
                        }

                        @media only screen and (max-width: 991px) {
                            .about-content a {
                                margin-top: 10px
                            }
                        }
                    </style>
                    <div class="row">
                        <div class="col-sm-12 ui-resizable" data-type="container-content">
                            <div class="text-center" data-type="component-text">
                                <div class="photo-panel"><img class="w-60 h-60 img-cover"
                                        src="{{ asset('uploads/main/images/15-09-2024/66e7009b50afa.png') }}"
                                        {{-- src="https://ecommerce.eimaths-th.com/public/uploads/main/images/15-09-2024/66e7009b50afa.png" --}} style="height: 80%; width: 80%;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="about section-margin-lg" data-type="component-text">
                        <div class="container">
                            <div class="row align-items-center">
                                <div class="col-lg-6">
                                    <div>
                                        <div class="row" id="counters">
                                            <div class="counter-shape">&nbsp;</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="about-content">
                                        <h4>&nbsp;</h4>
                                        <h4>&nbsp;</h4>
                                        <h4>&nbsp;</h4>
                                        <h4><strong>What is<br>eiMaths-TH&nbsp;Affiliate</strong></h4>
                                        <p>Affiliate เป็นระบบที่ให้คุณร่วมเป็นPartnerกับเรา
                                            โดยการโปรโมตผลิตภัณฑ์หรือบริการผ่านลิงก์เฉพาะของคุณ
                                            ทุกครั้งที่มีการซื้อหรือทำรายการผ่านลิงก์ของคุณ
                                            คุณจะได้รับค่าคอมมิชชั่นจากยอดขาย</p>
                                        <ul class="mb-4 mb-lg-5">
                                            <li class="d-flex flex-wrap"><span class="icon text-primary"><svg fill="none"
                                                        height="26" viewBox="0 0 26 26" width="26"
                                                        xmlns="http://www.w3.org/2000/svg">
                                                        <path
                                                            d="M13 25C19.6274 25 25 19.6274 25 13C25 6.37258 19.6274 1 13 1C6.37258 1 1 6.37258 1 13C1 19.6274 6.37258 25 13 25Z"
                                                            fill="#CEE8FF" stroke="currentColor" stroke-linecap="round"
                                                            stroke-linejoin="round" stroke-width="2"></path>
                                                        <path
                                                            d="M8.20312 14.2002L11.694 17.8002C13.3367 13.0814 14.7048 11.0108 17.8031 8.2002"
                                                            stroke="currentColor" stroke-linecap="round"
                                                            stroke-linejoin="round" stroke-width="2"></path>
                                                    </svg> </span> <span class="content"><span
                                                        style="color: var(--system_paragraph_color); background-color: var(--bs-body-bg); font-family: var(--font_family2); font-size: var(--bs-body-font-size); text-align: var(--bs-body-text-align);">คุณสามารถสร้างรายได้จากการแนะนำลูกค้าใหม่ผ่านลิงก์ที่เราให้
                                                        ระบบของเราจะติดตามผลและรายงานยอดคลิก ยอดขาย
                                                        และค่าคอมมิชชั่นแบบเรียลไทม์
                                                        เพื่อให้คุณทราบถึงผลการทำงานของคุณอย่างต่อเนื่อง</span></span>
                                            </li>
                                            <li class="d-flex flex-wrap"><span class="icon text-primary"><svg
                                                        fill="none" height="26" viewBox="0 0 26 26" width="26"
                                                        xmlns="http://www.w3.org/2000/svg">
                                                        <path
                                                            d="M13 25C19.6274 25 25 19.6274 25 13C25 6.37258 19.6274 1 13 1C6.37258 1 1 6.37258 1 13C1 19.6274 6.37258 25 13 25Z"
                                                            fill="#CEE8FF" stroke="currentColor" stroke-linecap="round"
                                                            stroke-linejoin="round" stroke-width="2"></path>
                                                        <path
                                                            d="M8.20312 14.2002L11.694 17.8002C13.3367 13.0814 14.7048 11.0108 17.8031 8.2002"
                                                            stroke="currentColor" stroke-linecap="round"
                                                            stroke-linejoin="round" stroke-width="2"></path>
                                                    </svg> </span> <span class="content"><span
                                                        style="color: var(--system_paragraph_color); background-color: var(--bs-body-bg); font-family: var(--font_family2); font-size: var(--bs-body-font-size); text-align: var(--bs-body-text-align);">เพื่อเข้าร่วมโปรแกรม
                                                        คุณต้องยอมรับและปฏิบัติตามข้อตกลงที่ระบุไว้
                                                        ซึ่งรวมถึงการไม่ใช้วิธีการโปรโมตที่ผิดกฎหมายหรือสแปม
                                                        และการปฏิบัติตามแนวทางที่ระบุในนโยบายของเรา</span></span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12 ui-resizable" data-type="container-content">
                    <div data-aoraeditor-categories="Affiliate Page"
                        data-aoraeditor-title="Affiliate How Does Work Section"
                        data-preview="{{ asset('frontend/infixlmstheme/img/snippets/preview/affiliate/how_work_section.png') }}"
                        {{-- data-preview="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/img/snippets/preview/affiliate/how_work_section.png" --}} data-type="component-text">
                        <div class="lms_section section_padding2 pb-0">
                            <div class="container">
                                <div class="row justify-content-center">
                                    <div class="col-xl-10">
                                        <div class="work_title_area">
                                            <div class="section__title4 "><span class="subheading_text">Affiliate
                                                    ของเราทำงานอย่างไร&nbsp;<span
                                                        style="color: var(--system_primery_color); background-color: var(--bs-body-bg); font-family: var(--font_family2); text-align: var(--bs-body-text-align);">?</span></span>
                                                <h4><strong>เราได้ปรับการรายงานและการติดตามผลสำหรับ
                                                        Partner<br>ให้ราบรื่นยิ่งขึ้น</strong></h4>
                                            </div>
                                            <div class="work_title_desc">
                                                <p>ระบบของเราช่วยให้ Affiliate
                                                    สามารถติดตามผลการทำงานได้อย่างละเอียด<br>ไม่ว่าจะเป็นจำนวนคลิก
                                                    ยอดขาย
                                                    และรายได้ที่เกิดจากการแนะนำของคุณ<br>ข้อมูลเหล่านี้สามารถดูได้ผ่านแดชบอร์ดที่ใช้งานง่าย
                                                </p>
                                                <p>ระบบรายงานจะแสดงข้อมูลแบบเรียลไทม์<br>เพื่อให้คุณทราบถึงผลลัพธ์และสถานะการทำงานได้อย่างแม่นยำและทันเหตุการณ์<br>ช่วยให้คุณปรับกลยุทธ์ได้อย่างมีประสิทธิภาพ<br><br>เรามีแบนเนอร์,
                                                    ลิงก์ติดตาม, และเนื้อหาสำหรับการโฆษณาที่
                                                    Affiliate<br>สามารถนำไปใช้โปรโมท
                                                    คู่มือหรือบทความที่ให้คำแนะนำเกี่ยวกับวิธีการทำการตลาด<br>เพื่อเพิ่มโอกาสในการขาย
                                                </p>
                                            </div>
                                        </div>
                                        <div class="row justify-content-center">
                                            <div class="col-xl-4 col-lg-4 col-md-6">
                                                <div class="process_widget_box text-center mb_30">
                                                    <div class="icon"><img alt=""
                                                            src="{{ asset('frontend/infixlmstheme/img/snippets/preview/affiliate/process_icon_1.svg') }}">
                                                        {{-- src="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/img/snippets/preview/affiliate/process_icon_1.svg"> --}}
                                                    </div>
                                                    <h4>ลงทะเบียนกับเรา</h4>
                                                    <p>สมัครเป็น Partner
                                                        กับเราและเริ่มสร้างรายได้จากการโปรโมตผลิตภัณฑ์
                                                        ง่ายและรวดเร็ว!</p>
                                                    <p>&nbsp;</p>
                                                </div>
                                            </div>
                                            <div class="col-xl-4 col-lg-4 col-md-6">
                                                <div class="process_widget_box text-center mb_30">
                                                    <div class="icon"><img alt=""
                                                            src="{{ asset('frontend/infixlmstheme/img/snippets/preview/affiliate/process_icon_2.svg') }}">
                                                        {{-- src="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/img/snippets/preview/affiliate/process_icon_2.svg"> --}}
                                                    </div>
                                                    <h4>ยืนยัน Email</h4>
                                                    <p>ย่าลืมยืนยันอีเมลของคุณผ่านลิงก์ที่เราส่งไปยังกล่องจดหมายของคุณ
                                                        เพื่อเปิดใช้งานบัญชีและเริ่มต้นการใช้งานระบบของเราได้ทันที
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="col-xl-4 col-lg-4 col-md-6">
                                                <div class="process_widget_box text-center mb_30">
                                                    <div class="icon"><img alt=""
                                                            src="{{ asset('frontend/infixlmstheme/img/snippets/preview/affiliate/process_icon_3.svg') }}">
                                                        {{-- src="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/img/snippets/preview/affiliate/process_icon_3.svg"> --}}
                                                    </div>
                                                    <h4>สร้างAffiliateของคุณ</h4>
                                                    <p>สร้างบัญชี Affiliate
                                                        และเริ่มโปรโมตเพื่อรับค่าคอมมิชชั่นทันที<span
                                                            style="font-family: var(--font_family2); background-color: var(--bs-body-bg);">และรับค่าคอมมิชชั่นจากยอดขายที่มาจากลิงก์ของคุณ</span>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12 ui-resizable" data-type="container-content">
                    <div data-aoraeditor-categories="Affiliate Page"
                        data-aoraeditor-title="Affiliate Rules & Requirement Section"
                        data-preview="{{ asset('frontend/infixlmstheme/img/snippets/preview/affiliate/rules_section.png') }}"
                        {{-- data-preview="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/img/snippets/preview/affiliate/rules_section.png" --}} data-type="component-text">
                        <div class="lms_section section_padding2 affiliate_bg">
                            <div class="container">
                                <div class="row justify-content-center">
                                    <div class="col-xl-6 col-lg-8 col-md-8">
                                        <div class="section__title4 text-center margin_52"><span
                                                class="subheading_text">กฎและข้อกำหนด</span>
                                            <h3>สิ่งที่ควรทำและไม่ควรทำสำหรับ<br>พาร์ทเนอร์ Affiliate ของ eiMath-TH
                                            </h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="row justify-content-center">
                                    <div class="col-xl-10">
                                        <div class="Requirements_boxs mb_80">
                                            <div class="single_Requirements_box">
                                                <div class="Requirements_box_head">
                                                    <h3>สิ่งที่ควรทำ</h3>
                                                </div>
                                                <div class="Requirements_box_body">
                                                    <ul>
                                                        <li>
                                                            <div class="icon"><img alt=""
                                                                    src="{{ asset('frontend/infixlmstheme/img/snippets/preview/affiliate/green_check.svg') }}">
                                                                {{-- src="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/img/snippets/preview/affiliate/green_check.svg"> --}}
                                                            </div>
                                                            <p>มีส่วนร่วมกับชุมชน Facebook Tiktok IG&nbsp;
                                                                และแนะนำโซลูชันของเราในกระทู้ที่เกี่ยวข้อง</p>
                                                        </li>
                                                        <li>
                                                            <div class="icon"><img alt=""
                                                                    src="{{ asset('frontend/infixlmstheme/img/snippets/preview/affiliate/green_check.svg') }}">
                                                                {{-- src="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/img/snippets/preview/affiliate/green_check.svg"> --}}
                                                            </div>
                                                            <p>เขียนรีวิวและบทความในบล็อก
                                                                เกี่ยวกับผลิตภัณฑ์และโซลูชันของเรา</p>
                                                        </li>
                                                        <li>
                                                            <div class="icon"><img alt=""
                                                                    src="{{ asset('frontend/infixlmstheme/img/snippets/preview/affiliate/green_check.svg') }}">
                                                                {{-- src="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/img/snippets/preview/affiliate/green_check.svg"> --}}
                                                            </div>
                                                            <p>เผยแพร่ข้อมูลเชิงบวกเกี่ยวกับผลิตภัณฑ์และคุณสมบัติต่าง
                                                                ๆ ผ่านช่องทางโซเชียล</p>
                                                        </li>
                                                        <li>
                                                            <div class="icon"><img alt=""
                                                                    src="{{ asset('frontend/infixlmstheme/img/snippets/preview/affiliate/green_check.svg') }}">
                                                                {{-- src="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/img/snippets/preview/affiliate/green_check.svg"> --}}
                                                            </div>
                                                            <p>เผยแพร่การเปรียบเทียบผลิตภัณฑ์, white papers,
                                                                อินโฟกราฟิก, รูปภาพ และ กรณีศึกษา</p>
                                                        </li>
                                                        <li>
                                                            <div class="icon"><img alt=""
                                                                    src="{{ asset('frontend/infixlmstheme/img/snippets/preview/affiliate/green_check.svg') }}">
                                                                {{-- src="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/img/snippets/preview/affiliate/green_check.svg"> --}}
                                                            </div>
                                                            <p>สร้างวิดีโอสาธิตและบล็อกวิดีโอเกี่ยวกับผลิตภัณฑ์ของเรา
                                                                บนช่องทางของคุณเอง</p>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <div class="single_Requirements_box">
                                                <div class="Requirements_box_head style2">
                                                    <h3>สิ่งที่ไม่ควรทำ</h3>
                                                </div>
                                                <div class="Requirements_box_body">
                                                    <ul>
                                                        <li>
                                                            <div class="icon"><img alt=""
                                                                    src="{{ asset('frontend/infixlmstheme/img/snippets/preview/affiliate/red_cross.svg') }}">
                                                                {{-- src="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/img/snippets/preview/affiliate/red_cross.svg"> --}}
                                                            </div>
                                                            <p>ไม่ควรสแปมลิงก์
                                                                โดยไม่มีการสนทนาที่มีความหมายและในกระทู้ที่ไม่เกี่ยวข้อง
                                                            </p>
                                                        </li>
                                                        <li>
                                                            <div class="icon"><img alt=""
                                                                    src="{{ asset('frontend/infixlmstheme/img/snippets/preview/affiliate/red_cross.svg') }}">
                                                                {{-- src="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/img/snippets/preview/affiliate/red_cross.svg"> --}}
                                                            </div>
                                                            <p>ห้ามใช้วิธีการ Blackhat
                                                                และวิธีการที่ไม่ถูกต้องตามจริยธรรมในการจัดการกับเครื่องมือค้นหา
                                                            </p>
                                                        </li>
                                                        <li>
                                                            <div class="icon">&nbsp;</div>
                                                        </li>
                                                        <li>
                                                            <div class="icon">&nbsp;</div>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="apply_btn text-center mb_30"><a
                                                class="theme_btn min_windth_200 text-center" {{-- href="https://ecommerce.eimaths-th.com/affiliate/registration"> --}}
                                                href=" {{ route('affiliate.registration') }} ">
                                                สมัครเลย!!</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12 ui-resizable" data-type="container-content">
                    <div data-type="component-text">
                        <div class="breadcrumb_area bradcam_bg_1 position-relative">
                            <div class="breadcrumb_img w-100 h-100 position-absolute bottom-0 left-0 "><img alt=""
                                    class="w-100 h-100 img-cover"
                                    src="{{ asset('frontend/infixlmstheme/img/banner/bradcam_bg_1.jpg') }}">
                                {{-- src="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/img/banner/bradcam_bg_1.jpg"> --}}
                            </div>
                            <div class="container">
                                <div class="row">
                                    <div class="col-lg-9 offset-lg-1">
                                        <div class="breadcam_wrap"><span>About Company</span>
                                            <h3>The leading global marketplace.</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- test contact --}}

                    {{-- end test contact --}}
                    <style type="text/css" data-type="component-text">
                        .bg-deep-green {
                            background-color: #6CB552 !important
                        }

                        @media only screen and (min-width: 992px) and (max-width: 1279px) {
                            .counter .row {
                                margin-top: -70px
                            }
                        }

                        @media only screen and (max-width: 991px) {
                            .counter .row {
                                margin-top: -70px
                            }
                        }

                        @media only screen and (max-width: 767px) {
                            .counter>.container {
                                max-width: 92%
                            }
                        }

                        .counter-item {
                            padding: 40px;
                            border-radius: 40px;
                            position: relative;
                            z-index: 1;
                            max-width: 300px;
                        }

                        html[dir=rtl] .counter-item::before,
                        html[dir=rtl] .counter-item::after {
                            left: auto;
                            right: 0;
                        }

                        @media only screen and (min-width: 1280px) and (max-width: 1439px) {
                            .counter-item {
                                padding: 30px
                            }
                        }

                        @media only screen and (min-width: 992px) and (max-width: 1279px) {
                            .counter-item {
                                margin-top: 70px
                            }
                        }

                        @media only screen and (max-width: 991px) {
                            .counter-item {
                                margin-top: 70px
                            }
                        }

                        .counter-item::before,
                        .counter-item::after {
                            content: "";
                            width: 100%;
                            height: 100%;
                            border-radius: 40px;
                            position: absolute;
                            top: 0;
                            left: 0;
                            transform: rotate(5deg);
                            z-index: -1;
                            background-color: #EEE6F5;
                            opacity: .4
                        }

                        html[dir=rtl] .counter-item::before,
                        html[dir=rtl] .counter-item::after {
                            left: auto;
                            right: 0;
                        }

                        .counter-item::after {
                            z-index: -2;
                            opacity: .3;
                            transform: rotate(10deg)
                        }

                        .counter-item.bg-primary::before,
                        .counter-item.bg-primary::after {
                            background-color: var(--system_primery_color)
                        }

                        .counter-item.bg-deep-green::before,
                        .counter-item.bg-deep-green::after {
                            background-color: #6CB552
                        }

                        .counter-item.bg-orange::before,
                        .counter-item.bg-orange::after {
                            background-color: #FEB74C
                        }

                        .counter-item.bg-green::before,
                        .counter-item.bg-green::after {
                            background-color: #16CE8C
                        }

                        .counter-item-icon {
                            margin-bottom: 1rem
                        }

                        @media only screen and (min-width: 1280px) and (max-width: 1439px) {
                            .counter-item-icon {
                                margin-bottom: 10px
                            }
                        }

                        .counter-item label {
                            display: inline-flex
                        }

                        .counter-item h4 {
                            margin-bottom: 16px;
                            font-size: 24px;
                            line-height: 1
                        }

                        @media only screen and (min-width: 992px) and (max-width: 1279px) {
                            .counter-item h4 {
                                font-size: 22px
                            }
                        }

                        @media only screen and (max-width: 991px) {
                            .counter-item h4 {
                                font-size: 20px
                            }
                        }

                        @media only screen and (min-width: 1280px) and (max-width: 1439px) {
                            .counter-item h4 {
                                margin-bottom: 10px;
                                font-size: 21px
                            }
                        }

                        .counter-item h4 span {
                            display: block;
                            font-size: 36px;
                            line-height: 1.52778;
                            opacity: .8
                        }

                        @media only screen and (min-width: 1280px) and (max-width: 1439px) {
                            .counter-item h4 span {
                                font-size: 32px
                            }
                        }

                        @media only screen and (min-width: 992px) and (max-width: 1279px) {
                            .counter-item h4 span {
                                font-size: 30px
                            }
                        }

                        @media only screen and (min-width: 768px) and (max-width: 991px) {
                            .counter-item h4 span {
                                font-size: 28px
                            }
                        }

                        @media only screen and (max-width: 767px) {
                            .counter-item h4 span {
                                font-size: 24px
                            }
                        }

                        .counter-item p {
                            color: #fff;
                        }
                    </style>
                    <div class="about section-margin" data-type="component-text">
                        <div class="container">
                            <div class="row" id="counters">
                                <div class="col-xl-3 col-md-6">
                                    <div class="counter-item bg-primary text-white">
                                        <div class="counter-item-icon"><svg fill="none" height="37"
                                                viewBox="0 0 37 37" width="37" xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M18.5726 34.4966C16.7247 34.4966 16.7091 33.1522 15.1171 32.0052C12.3478 30.0101 8.09093 27.6623 4.70159 27.2026C4.09404 27.1354 3.53292 26.8455 3.12654 26.3889C2.72016 25.9323 2.49731 25.3413 2.50102 24.73V4.82601C2.501 4.46888 2.57834 4.11597 2.72773 3.79158C2.87712 3.46719 3.09501 3.17901 3.36641 2.94687C3.63303 2.71896 3.9451 2.55037 4.28187 2.45233C4.61865 2.35428 4.97244 2.329 5.31973 2.37819C10.4238 3.2253 15.093 5.76898 18.5726 9.59803V34.4966Z"
                                                    stroke="white" stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="3"></path>
                                                <path
                                                    d="M18.5724 34.4966C20.4203 34.4966 20.4359 33.1522 22.0279 32.0052C24.7973 30.0101 29.0541 27.6623 32.4434 27.2026C33.051 27.1354 33.6121 26.8455 34.0185 26.3889C34.4249 25.9323 34.6477 25.3413 34.644 24.73V4.82601C34.644 4.46888 34.5667 4.11597 34.4173 3.79158C34.2679 3.46719 34.05 3.17901 33.7786 2.94687C33.512 2.71896 33.1999 2.55037 32.8631 2.45233C32.5264 2.35428 32.1726 2.329 31.8253 2.37819C26.7212 3.2253 22.052 5.76898 18.5724 9.59803V34.4966Z"
                                                    stroke="white" stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="3"></path>
                                            </svg></div>
                                        <h4 class="currentColor"><span class="d-flex align-items-center">1</span>
                                            ข้อตกลงทั่วไป</h4>
                                        <p>ข้อตกลงนี้กำหนดเงื่อนไขและข้อกำหนดในการเข้าร่วมโปรแกรม Affiliate
                                            รวมถึงสิทธิและความรับผิดชอบของทั้งสองฝ่าย</p>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-md-6">
                                    <div class="counter-item bg-deep-green text-white">
                                        <div class="counter-item-icon"><svg fill="none" height="37"
                                                viewBox="0 0 40 37" width="40" xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M27.1709 18.7017C30.9915 18.7017 34.0887 15.6045 34.0887 11.7839C34.0887 8.82843 32.2354 6.30583 29.6275 5.31494"
                                                    stroke="white" stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="3"></path>
                                                <path
                                                    d="M15.5303 17.3302C19.6718 17.3302 23.0292 13.9729 23.0292 9.83137C23.0292 5.68987 19.6718 2.33252 15.5303 2.33252C11.3888 2.33252 8.03149 5.68987 8.03149 9.83137C8.03149 13.9729 11.3888 17.3302 15.5303 17.3302Z"
                                                    stroke="white" stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="3"></path>
                                                <path
                                                    d="M27.0165 27.8659C30.6846 30.3089 28.2077 35.0208 23.8006 35.0208H7.25036C2.8432 35.0208 0.36634 30.3089 4.0344 27.8659C7.32457 25.6746 11.2759 24.3975 15.5255 24.3975C19.7751 24.3975 23.7264 25.6746 27.0165 27.8659Z"
                                                    stroke="white" stroke-width="3"></path>
                                                <path
                                                    d="M36.6195 28.4199L35.788 29.6684L36.6195 28.4199ZM23.1628 33.5205C22.3344 33.5205 21.6628 34.192 21.6628 35.0205C21.6628 35.8489 22.3344 36.5205 23.1628 36.5205V33.5205ZM34.2513 25.4271C33.4917 25.0965 32.6079 25.4443 32.2774 26.2039C31.9468 26.9635 32.2946 27.8473 33.0542 28.1779L34.2513 25.4271ZM23.1628 36.5205H33.6527V33.5205H23.1628V36.5205ZM33.6527 36.5205C36.2184 36.5205 38.3575 35.1415 39.2822 33.2141C39.7481 32.2428 39.9061 31.1179 39.6022 30.0064C39.2959 28.8864 38.5558 27.9073 37.451 27.1715L35.788 29.6684C36.3752 30.0594 36.6185 30.4688 36.7084 30.7977C36.8007 31.1351 36.7678 31.5195 36.5773 31.9165C36.189 32.726 35.1528 33.5205 33.6527 33.5205V36.5205ZM33.0542 28.1779C34.0124 28.5949 34.9267 29.0947 35.788 29.6684L37.451 27.1715C36.4436 26.5005 35.3735 25.9155 34.2513 25.4271L33.0542 28.1779Z"
                                                    fill="white"></path>
                                            </svg></div>
                                        <h4 class="currentColor"><span class="d-flex align-items-center"><span
                                                    class="counter p-0 m-0 ">2</span></span>ความเป็นส่วนตัว</h4>
                                        <p>อธิบายวิธีการรวบรวม ใช้ และปกป้องข้อมูลของพาทเนอร์
                                            รวมถึงสิทธิ์ในการจัดการข้อมูล</p>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-md-6">
                                    <div class="counter-item bg-orange text-white">
                                        <div class="counter-item-icon"><svg fill="none" height="40"
                                                viewBox="0 0 40 40" width="40" xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M20 38C29.9411 38 38 29.9411 38 20C38 10.0589 29.9411 2 20 2C10.0589 2 2 10.0589 2 20C2 29.9411 10.0589 38 20 38Z"
                                                    stroke="white" stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="3"></path>
                                                <path
                                                    d="M8.23064 26.9231C9.51592 26.9231 10.7486 26.4125 11.6574 25.5037C12.5662 24.5948 13.0768 23.3622 13.0768 22.0769V17.9231C13.0768 16.6378 13.5874 15.4051 14.4962 14.4963C15.405 13.5875 16.6377 13.0769 17.9229 13.0769C19.2082 13.0769 20.4409 12.5663 21.3497 11.6575C22.2585 10.7487 22.7691 9.51604 22.7691 8.23076V2.21168C21.8665 2.07231 20.9417 2 20 2C10.0589 2 2 10.0589 2 20C2 22.4533 2.49078 24.7919 3.37953 26.9231H8.23064Z"
                                                    stroke="white" stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="3"></path>
                                                <path
                                                    d="M37.9978 19.722C36.6117 19.0032 35.0748 18.6238 33.5134 18.6152H27.615C26.3297 18.6152 25.097 19.1258 24.1882 20.0346C23.2794 20.9435 22.7688 22.1761 22.7688 23.4614C22.7688 24.7467 23.2794 25.9793 24.1882 26.8881C25.097 27.797 26.3297 28.3075 27.615 28.3075C28.533 28.3075 29.4135 28.6722 30.0626 29.3214C30.7118 29.9706 31.0765 30.851 31.0765 31.7691V34.1783H31.0904C35.2707 30.9039 37.9663 25.8208 37.9996 20.1069V19.8928C37.9992 19.8358 37.9986 19.7789 37.9978 19.722Z"
                                                    stroke="white" stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="3"></path>
                                            </svg></div>
                                        <h4 class="currentColor"><span class="d-flex align-items-center">3</span>
                                            นโยบายการใช้งาน</h4>
                                        <p>กำหนดวิธีการใช้ระบบและเครื่องมือในโปรแกรม Affiliate
                                            รวมถึงข้อห้ามที่ต้องปฏิบัติตาม</p>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-md-6">
                                    <div class="counter-item bg-green text-white">
                                        <div class="counter-item-icon"><svg fill="none" height="36"
                                                viewBox="0 0 40 36" width="40" xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M20.25 6.86275L19.0872 7.8104C19.3721 8.15992 19.7991 8.36275 20.25 8.36275C20.7009 8.36275 21.1279 8.15992 21.4127 7.8104L20.25 6.86275ZM2.25 11.9473L0.750164 11.9251C0.750055 11.9325 0.75 11.9399 0.75 11.9473H2.25ZM38.25 11.9473H39.75C39.75 11.9399 39.7499 11.9325 39.7498 11.9251L38.25 11.9473ZM3.74984 11.9695C3.80301 8.37207 5.86454 5.0043 8.66757 3.69812C10.0351 3.06085 11.6143 2.89066 13.3317 3.4358C15.0693 3.98734 17.0448 5.30437 19.0872 7.8104L21.4127 5.91511C19.1068 3.08573 16.6732 1.34896 14.2393 0.576395C11.7853 -0.202583 9.43065 0.032806 7.40043 0.978862C3.40788 2.83934 0.817805 7.34926 0.750164 11.9251L3.74984 11.9695ZM20.25 32.6024C20.1152 32.6024 19.7289 32.5356 19.0552 32.2606C18.4195 32.0012 17.6408 31.6037 16.7661 31.0708C15.018 30.0057 12.967 28.449 11.0133 26.5203C7.06408 22.6216 3.75 17.4461 3.75 11.9473H0.75C0.75 18.6163 4.71522 24.5183 8.90565 28.6552C11.0218 30.7443 13.2557 32.4449 15.2052 33.6327C16.1793 34.2262 17.1026 34.704 17.9217 35.0383C18.7029 35.3571 19.5244 35.6024 20.25 35.6024V32.6024ZM39.7498 11.9251C39.6822 7.34925 37.0921 2.83934 33.0995 0.978861C31.0693 0.0328063 28.7147 -0.202582 26.2606 0.576394C23.8267 1.34896 21.3932 3.08573 19.0872 5.91511L21.4127 7.8104C23.4551 5.30437 25.4307 3.98734 27.1683 3.4358C28.8856 2.89066 30.4648 3.06085 31.8324 3.69812C34.6354 5.0043 36.697 8.37207 36.7502 11.9695L39.7498 11.9251ZM20.25 35.6024C20.9755 35.6024 21.797 35.3571 22.5782 35.0383C23.3973 34.704 24.3206 34.2262 25.2948 33.6327C27.2443 32.4449 29.4782 30.7443 31.5943 28.6552C35.7848 24.5184 39.75 18.6163 39.75 11.9473H36.75C36.75 17.4461 33.4359 22.6216 29.4867 26.5203C27.533 28.449 25.482 30.0057 23.7338 31.0708C22.8592 31.6037 22.0805 32.0012 21.4447 32.2606C20.771 32.5356 20.3848 32.6024 20.25 32.6024V35.6024Z"
                                                    fill="white"></path>
                                            </svg></div>
                                        <h4 class="currentColor"><span class="d-flex align-items-center"><span
                                                    class="counter p-0 m-0 ">4</span></span>การเปลี่ยนแปลง<br>และการยกเลิก
                                        </h4>
                                        <p>ระบุขั้นตอนเมื่อมีการเปลี่ยนแปลงนโยบายหรือยกเลิกโปรแกรม
                                            รวมถึงวิธีการจัดการของ Partner</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modules_area section__padding_custom" data-type="component-text">
                        <div class="container">
                            <div class="row justify-content-center">
                                <div class="col-lg-6 col-md-8">
                                    <h3>&nbsp;</h3>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="modile_main_wrap">
                                        <div class="single_module text-center">
                                            <p style="text-align: left;"><a href="javascript:void(0)">&nbsp;</a>1.1
                                                การสมัครเข้าร่วมโปรแกรม
                                                Affiliate<br>ผู้ใช้ที่ต้องการเข้าร่วมโปรแกรม Affiliate
                                                จะต้องสมัครสมาชิกและกรอกข้อมูลที่จำเป็นอย่างครบถ้วนและถูกต้อง
                                                การสมัครเข้าร่วมโปรแกรมหมายความว่าผู้ใช้ยอมรับข้อตกลงและเงื่อนไขทั้งหมดที่ระบุไว้ในเอกสารนี้<br><br>1.2
                                                การใช้ลิงก์ Affiliate<br>ผู้ใช้ที่เข้าร่วมโปรแกรม Affiliate
                                                จะได้รับลิงก์พิเศษเพื่อใช้ในการแนะนำลูกค้าใหม่
                                                หากมีการสั่งซื้อคอร์สเรียนผ่านลิงก์นั้น
                                                ผู้ใช้จะได้รับค่าคอมมิชชั่นตามอัตราที่กำหนดไว้<br><br>1.3
                                                การชำระเงินค่าคอมมิชชั่น<br>ค่าคอมมิชชั่นจะถูกจ่ายให้กับผู้ใช้ทุก ๆ
                                                สิ้นเดือน หลังจากผ่านกระบวนการตรวจสอบความถูกต้องของยอดขาย
                                                ผู้ใช้ต้องมีบัญชีธนาคารที่ถูกต้องเพื่อรับเงินค่าคอมมิชชั่น</p>
                                        </div>
                                        <div class="single_module text-center">
                                            <div style="text-align: left;">2.1
                                                การเก็บข้อมูลส่วนบุคคล<br>ข้อมูลส่วนบุคคลของผู้ใช้ที่สมัครเข้าร่วมโปรแกรม
                                                Affiliate
                                                จะถูกเก็บรักษาอย่างปลอดภัยและจะไม่ถูกนำไปใช้ในทางที่ผิดหรือเผยแพร่ให้บุคคลภายนอกโดยไม่ได้รับความยินยอม
                                                ยกเว้นในกรณีที่จำเป็นต้องเปิดเผยตามกฎหมาย<br><br>2.2
                                                การใช้ข้อมูล<br>ข้อมูลส่วนบุคคลที่เก็บรวบรวมจะถูกใช้เพื่อวัตถุประสงค์ในการบริหารจัดการโปรแกรม
                                                Affiliate และเพื่อปรับปรุงการบริการเท่านั้น<br><br>2.3
                                                การคุ้มครองข้อมูล<br>เรามีมาตรการรักษาความปลอดภัยที่เข้มงวดเพื่อปกป้องข้อมูลของผู้ใช้จากการเข้าถึงโดยไม่ได้รับอนุญาตหรือการเปิดเผยข้อมูลโดยไม่ได้รับอนุญาต
                                            </div>
                                        </div>
                                        <div class="single_module text-center" style="text-align: left;">
                                            <div style="text-align: left;">3.1
                                                การส่งเสริมการขาย<br>ผู้ใช้ต้องส่งเสริมการขายคอร์สเรียนโดยไม่ละเมิดกฎหมายหรือข้อบังคับใด
                                                ๆ และไม่ใช้วิธีการที่เป็นการหลอกลวงหรือเป็นการรบกวน<br><br>3.2
                                                ความรับผิดชอบของ
                                                Affiliate<br>ผู้ใช้ต้องรับผิดชอบต่อการกระทำของตนเองในการโปรโมตคอร์สเรียนและการใช้ลิงก์
                                                Affiliate หากมีการกระทำใดที่ฝ่าฝืนกฎระเบียบและเงื่อนไข
                                                ผู้ใช้จะต้องรับผิดชอบต่อความเสียหายที่เกิดขึ้นทั้งหมด<br><br>3.3
                                                การยุติการเป็น Affiliate<br>เราขอสงวนสิทธิ์ในการยุติบัญชี Affiliate
                                                ของผู้ใช้ได้ทุกเมื่อ
                                                หากพบว่ามีการละเมิดข้อตกลงหรือนโยบายการใช้งานที่กำหนดไว้</div>
                                        </div>
                                        <div class="single_module text-center">
                                            <p style="text-align: left;">4.1
                                                เราขอสงวนสิทธิ์ในการแก้ไขหรือปรับเปลี่ยนข้อตกลง,
                                                นโยบายความเป็นส่วนตัว, และนโยบายการใช้งาน Affiliate
                                                โดยไม่ต้องแจ้งให้ทราบล่วงหน้า การเปลี่ยนแปลงใด ๆ
                                                จะมีผลบังคับใช้ทันทีเมื่อมีการประกาศบนเว็บไซต์<br><br>4.2&nbsp;ในกรณีที่บริษัทตัดสินใจยกเลิกโปรแกรม
                                                Affiliate
                                                เราจะแจ้งให้พาทเนอร์ทราบล่วงหน้าผ่านทางอีเมลหรือช่องทางสื่อสารที่เหมาะสม
                                                การยกเลิกจะไม่มีผลกระทบต่อค่าคอมมิชชั่นที่ได้สะสมไว้ก่อนการยกเลิก<br><br>4.3
                                                บริษัทขอสงวนสิทธิ์ในการระงับบัญชี Affiliate หรือยุติความร่วมมือ
                                                หากพบว่ามีการกระทำที่ละเมิดข้อกำหนดหรือมีผลกระทบต่อผลประโยชน์ของบริษัท
                                                การกระทำดังกล่าวอาจส่งผลให้สูญเสียสิทธิ์ในค่าคอมมิชชั่นที่ยังไม่ได้รับ
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12 ui-resizable" data-type="container-content">
                    <div data-aoraeditor-categories="Affiliate Page"
                        data-aoraeditor-title="Affiliate Frequently Asked Questions Section"
                        data-preview="{{ asset('frontend/infixlmstheme/img/snippets/preview/affiliate/frequntlya_ask_section.png') }}"
                        {{-- data-preview="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/img/snippets/preview/affiliate/frequntlya_ask_section.png" --}} data-type="component-text">
                        <div class="affliate_faq-section">
                            <div class="container">
                                <div class="row justify-content-center">
                                    <div class="col-xl-6 col-lg-8 col-md-8">
                                        <div class="section__title4 text-center margin_52"><span
                                                class="subheading_text">รีวิวจากการใช้&nbsp;Affiliate</span>
                                            <h4><strong>ใช้&nbsp;Affiliate แล้วเป็นไรอย่างไร</strong></h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xl-4 col-lg-4 col-md-6">
                                        <div class="affiliate_faq_box mb_25">
                                            <h4>เริ่มต้น</h4>
                                            <p>คุณเมย์เริ่มต้นเข้าร่วมโปรแกรม Affiliate
                                                โดยมีเป้าหมายเพียงเพื่อสร้างรายได้เสริมจากการโปรโมตคอร์สเรียนที่เธอชื่นชอบในช่วงเวลาว่าง
                                                ๆ เธอเริ่มต้นด้วยการโปรโมตลิงก์ Affiliate
                                                ผ่านทางโซเชียลมีเดียของเธอเอง เช่น Facebook และ Instagram
                                                และใช้วิธีการสร้างเนื้อหาที่เกี่ยวข้องกับคอร์สเรียนที่เธอแนะนำ</p>
                                        </div>
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-6">
                                        <div class="affiliate_faq_box mb_25">
                                            <h4>กลยุทธ์</h4>
                                            <p>คุณเมย์เชื่อว่าการทำคอนเทนต์ที่มีคุณภาพเป็นกุญแจสำคัญในการดึงดูดความสนใจของกลุ่มเป้าหมาย
                                                เธอจึงใช้เวลาในการเขียนบทความรีวิวคอร์สเรียน,
                                                การทำวิดีโอที่แนะนำเนื้อหาคอร์ส
                                                และการแชร์ประสบการณ์จริงจากการเรียนรู้<br><br>เธอใช้โซเชียลมีเดียในการโปรโมตลิงก์
                                                Affiliate โดยเน้นการสร้างโพสต์ที่ให้คุณค่ากับผู้ติดตาม เช่น
                                                การให้ความรู้, เคล็ดลับ, และแนวทางการพัฒนาตัวเอง
                                                ซึ่งช่วยให้ผู้ติดตามเชื่อมั่นในคำแนะนำของเธอ</p>
                                        </div>
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-6">
                                        <div class="affiliate_faq_box mb_25">
                                            <h4>การเข้าถึงกลุ่มเป้าหมายที่ตรงจุด</h4>
                                            <p>คุณเมย์เน้นการเข้าถึงกลุ่มเป้าหมายที่มีความสนใจในคอร์สเรียนที่เธอโปรโมต
                                                โดยใช้การโฆษณาที่กำหนดกลุ่มเป้าหมายเฉพาะเจาะจง เช่น
                                                ผู้ที่สนใจการพัฒนาทักษะใหม่ ๆ
                                                หรือผู้ที่กำลังมองหาการเรียนรู้เพิ่มเติมในสายงานเฉพาะ</p>
                                        </div>
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-6">
                                        <div class="affiliate_faq_box mb_25">
                                            <h4>การตอบสนองและให้ความช่วยเหลือที่รวดเร็ว</h4>
                                            <p>คุณเมย์มักจะตอบคำถามและข้อสงสัยของผู้ติดตามในทันทีเมื่อมีการสอบถามเกี่ยวกับคอร์สเรียนที่เธอแนะนำ
                                                การให้ข้อมูลที่เป็นประโยชน์และตรงไปตรงมาช่วยให้ผู้ติดตามตัดสินใจซื้อได้ง่ายขึ้น
                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-6">
                                        <div class="affiliate_faq_box mb_25">
                                            <h4>ผลลัพธ์ที่ได้รับ</h4>
                                            <p>จากการใช้กลยุทธ์เหล่านี้ คุณเมย์สามารถสร้างรายได้จากโปรแกรม Affiliate
                                                ได้มากกว่า 50,000 บาทต่อเดือนอย่างต่อเนื่อง นอกจากนี้
                                                เธอยังได้รับการยอมรับจากกลุ่มเป้าหมายและผู้ติดตามว่าเป็นผู้เชี่ยวชาญในด้านการเรียนรู้และพัฒนาตัวเอง
                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-6">
                                        <div class="affiliate_faq_box mb_25">
                                            <h4>คำแนะนำ</h4>
                                            <p>รู้จักผู้ติดตามของคุณ:
                                                เข้าใจกลุ่มเป้าหมายและสร้างเนื้อหาที่ตอบโจทย์ความต้องการ</p>
                                            <p>ให้คุณค่ามากกว่าการขาย:
                                                สร้างความน่าเชื่อถือโดยการให้ข้อมูลที่เป็นประโยชน์ ไม่ใช่แค่ขายของ
                                            </p>
                                            <p>ทดลองและปรับปรุง: ทดลองวิธีใหม่ ๆ
                                                เพื่อพัฒนากลยุทธ์การตลาดให้ดียิ่งขึ้น</p>
                                            <p>กรณีศึกษานี้แสดงให้เห็นว่าความสำเร็จในโปรแกรม Affiliate
                                                มาจากการวางแผนและใช้กลยุทธ์ที่เหมาะสมในการดึงดูดและสร้างความเชื่อมั่นในกลุ่มเป้าหมาย
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div data-type="component-text">
                    <div class="breadcrumb_area bradcam_bg_1 position-relative">
                        <div class="breadcrumb_img w-100 h-100 position-absolute bottom-0 left-0 "><img alt=""
                                class="w-100 h-100 img-cover"
                                src="{{ asset('frontend/infixlmstheme/img/banner/bradcam_bg_1.jpg') }}">
                            {{-- src="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/img/banner/bradcam_bg_1.jpg"> --}}
                        </div>
                        <div class="container">
                            <div class="row">
                                <div class="col-lg-9 offset-lg-1">
                                    <div class="breadcam_wrap"><span>About Company</span>
                                        <h3>The leading global marketplace.</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="contact_section " data-type="component-text">
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-xl-10">
                                <div class="contact_address">
                                    <div class="row justify-content-center">
                                        <div class="col-xl-12">
                                            <div class="row justify-content-between">
                                                <div class="col-lg-5">
                                                    <div class="contact_info mb_30">
                                                        <h4 class="mb-0">ข้อมูลการติดต่อ</h4>
                                                        <div class="address_lines">
                                                            <div class="single_address_line d-flex">
                                                                <div class="address_info">
                                                                    <p>The Crystal SB ราชพฤกษ์ ชั้น 3, 555/9
                                                                        อำเภอบางกรวย จังหวัดนนทบุรี 11130</p>
                                                                </div>
                                                            </div>
                                                            <div class="single_address_line d-flex">
                                                                <div class="address_info">
                                                                    <p>0 616-208-666</p>
                                                                </div>
                                                            </div>
                                                            <div class="single_address_line d-flex">
                                                                <div class="address_info">
                                                                    <p>info@eimaths-th.com</p>
                                                                    <p>ส่งคำถามของคุณมาหาเราได้ตลอดเวลา!</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
@endsection
