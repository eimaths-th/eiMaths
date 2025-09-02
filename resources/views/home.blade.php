@extends('layouts.app')

@section('style')
    <style>
        .ui-datepicker-calendar {
            display: none;
        }

        /* Premium Dashboard Styles */
        .dashboard-premium {
            background: #ffffff !important;
            /* background: linear-gradient(135deg, #ffffff 0%, #ffffff 0%); */
            /* background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%); */
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }

        .dashboard-premium::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: #ffffff !important;
            /* background: radial-gradient(circle at 20% 80%, rgba(120, 119, 198, 0.3) 0%, transparent 50%),
                        radial-gradient(circle at 80% 20%, rgba(255, 119, 48, 0.3) 0%, transparent 50%),
                        radial-gradient(circle at 40% 40%, rgba(120, 119, 198, 0.2) 0%, transparent 50%); */
            pointer-events: none;
        }

        /* Header Styling */
        .content-header {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            margin: 20px;
            padding: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .content-header h4 {
            color: rgb(36, 36, 36);
            font-weight: 600;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            margin: 0;
        }

        .content-header .form-control,
        .content-header .input-group-text {
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: #333;
        }

        .content-header .btn {
            background: linear-gradient(45deg, #ff6b35, #f7931e);
            border: none;
            color: white;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(255, 107, 53, 0.3);
            transition: all 0.3s ease;
        }

        .content-header .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 107, 53, 0.4);
        }

        /* Premium Info Boxes */
        .info-box {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
            position: relative;
            margin-bottom: 20px;
        }

        .info-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #ff6b35, #f7931e, #ffaa00);
            transform: scaleX(0);
            transition: transform 0.4s ease;
        }

        .info-box:hover::before {
            transform: scaleX(1);
        }

        .info-box:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            background: rgba(255, 255, 255, 1);
        }

        .info-box-icon {
            background: linear-gradient(135deg, #ff6b35, #f7931e) !important;
            border-radius: 15px;
            transition: all 0.4s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .info-box-icon::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            transition: all 0.4s ease;
            transform: translate(-50%, -50%);
        }

        .info-box:hover .info-box-icon::before {
            width: 100px;
            height: 100px;
        }

        .info-box:hover .info-box-icon {
            transform: scale(1.1) rotate(5deg);
            background: linear-gradient(135deg, #f7931e, #ffaa00) !important;
        }

        .info-box-icon i {
            font-size: 24px;
            color: white;
            z-index: 2;
            position: relative;
        }

        .info-box-content {
            padding: 15px 20px;
            transition: all 0.3s ease;
        }

        .info-box:hover .info-box-content {
            padding-left: 25px;
        }

        .info-box-text {
            font-size: 14px;
            font-weight: 500;
            color: #6c757d;
            margin-bottom: 5px;
            transition: all 0.3s ease;
        }

        .info-box:hover .info-box-text {
            color: #495057;
            font-weight: 600;
        }

        .info-box-number {
            font-size: 28px;
            font-weight: 700;
            color: #2c3e50;
            transition: all 0.3s ease;
            background: linear-gradient(45deg, #ff6b35, #f7931e);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .info-box:hover .info-box-number {
            transform: scale(1.1);
            text-shadow: 0 2px 4px rgba(255, 107, 53, 0.3);
        }

        /* Premium Cards */
        .card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }

        .card-header {
            background: linear-gradient(135deg, #f7931e, #ff6b35);
            color: white;
            border: none;
            padding: 20px;
            border-radius: 20px 20px 0 0;
        }

        .card-title {
            font-weight: 600;
            margin: 0;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .card-title i {
            margin-right: 10px;
            font-size: 18px;
        }

        .card-body {
            padding: 25px;
            background: rgba(255, 255, 255, 0.98);
        }

        /* Link Styling */
        a.text-dark {
            text-decoration: none;
            color: inherit;
            display: block;
        }

        a.text-dark:hover {
            text-decoration: none;
            color: inherit;
        }

        /* Content Section */
        .content {
            position: relative;
            z-index: 1;
            padding: 20px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .dashboard-premium {
                padding: 10px;
            }

            .content-header {
                margin: 10px;
                padding: 15px;
            }

            .info-box:hover {
                transform: translateY(-4px) scale(1.01);
            }

            .info-box-number {
                font-size: 24px;
            }
        }

        /* Animation for loading */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .info-box {
            animation: fadeInUp 0.6s ease forwards;
        }

        .info-box:nth-child(1) { animation-delay: 0.1s; }
        .info-box:nth-child(2) { animation-delay: 0.2s; }
        .info-box:nth-child(3) { animation-delay: 0.3s; }
        .info-box:nth-child(4) { animation-delay: 0.4s; }
        .info-box:nth-child(5) { animation-delay: 0.5s; }
        .info-box:nth-child(6) { animation-delay: 0.6s; }
        .info-box:nth-child(7) { animation-delay: 0.7s; }
        .info-box:nth-child(8) { animation-delay: 0.8s; }
    </style>
@endsection

@section('content')
<div class="dashboard-premium">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 justify-content-end">
                <div class="col-sm-7">
                    <h4>
                        <i class="fa-solid fa-chart-pie"></i>
                        📊 eiMaths Dashboard
                        <small class="d-block mt-1" style="font-size: 14px; opacity: 0.8;">ระบบจัดการข้อมูลสถิติ</small>
                    </h4>
                </div>
                <div class="col-sm-2">
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <i class="fas fa-lg fa-building nav-icon"></i>
                                </span>
                            </div>
                            <select name="dcentre" id="dcentre" class="form-control"
                                @cannot('all-centre') disabled @endcannot>
                                @foreach ($centre as $key2)
                                    @if ($key2->id != 4 && $key2->id != 5)
                                        <option value="{{ $key2->id }}"
                                            @if (Auth::user()->department->id == (int) $key2->id) selected @endif>
                                            {{-- {{ $key2->code }} --}}
                                            {{ $key2->name }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                            {{-- {{ dd((int) $key2->id) }} --}}
                        </div>

                    </div>
                </div>
                <div class="col-sm-2">

                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <i class="far fa-calendar-alt"></i>
                                </span>
                            </div>
                            <input type="text" class="form-control float-right" id="reservation" name="reservation">
                        </div>
                    </div>
                </div>
                <div class="col-xs-1 col-sm-1 col-md-1">
                    <div class="form-group">
                        <button type="button" class="form-control btn bg-gradient-success" id="SearchButtons">
                            <i class="fas fa-search"></i></button>

                    </div>
                </div>
                {{--  <div class="col-xs-1 col-sm-1 col-md-1" style="align-items: flex-end;">
                    <div class="form-group">
                        <button type="button" class="form-control btn bg-gradient-warning" id="resetSearchButton"><i
                                class="fa-solid fa-rotate"></i></button>
                    </div>
                </div> --}}
            </div>
        </div>
    </section>


    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">


                    <div class="row">

                        <div class="col-md-6 col-sm-6 col-lg-6  col-xl-3 col-12">
                            <a href="{{ route('departments') }}" target="_blank" class="text-dark">
                                <div class="info-box">
                                    <span class="info-box-icon bg-warning"><i class="fa-solid fa-building"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Total Centre</span>
                                        <span class="info-box-number" id="tcentre">0</span>
                                    </div>

                                </div>
                            </a>
                        </div>
                        <div class="col-md-6 col-sm-6 col-lg-6  col-xl-3 col-12">
                            {{-- return to contacts with centre --}}
                            <a id="contacts-link" href="{{ route('contacts', ['centre' => Auth::user()->department->id]) }}"
                                target="_blank" class="text-dark">
                                <div class="info-box">
                                    <span class="info-box-icon bg-warning"><i class="fa-solid fa-graduation-cap"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Total Student</span>
                                        <span class="info-box-number" id="tstudent">0</span>
                                    </div>

                                </div>
                            </a>
                        </div>
                        <div class="col-md-6 col-sm-6 col-lg-6  col-xl-3 col-12">
                            {{-- return to user with role teacher --}}
                            <a id="teacher-link"
                                href="{{ route('users.index', ['role' => 'Teacher', 'centre' => Auth::user()->department->id]) }}"
                                target="_blank" class="text-dark">
                                <div class="info-box">
                                    <span class="info-box-icon bg-warning"><i
                                            class="fa-solid fa-chalkboard-user"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Total Teacher</span>
                                        <span class="info-box-number" id="tteacher">0</span>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6 col-sm-6 col-lg-6  col-xl-3 col-12">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning"><i class="fa-solid fa-book"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Today Study Student</span>
                                    <span class="info-box-number" id="tstudy">0</span>
                                </div>

                            </div>
                        </div>
                        <div class="col-md-6 col-sm-6 col-lg-6  col-xl-3 col-12">
                            {{-- return to receipt with centre --}}
                            <a href="{{ route('receipts', ['centre' => Auth::user()->department->id]) }}" target="_blank"
                                class="text-dark receipt-link">
                                <div class="info-box">
                                    <span class="info-box-icon bg-warning"><i
                                            class="fa-solid fa-file-invoice-dollar"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text ">Total Receipt</span>
                                        <span class="info-box-number" id="treceipt">0</span>
                                    </div>

                                </div>
                            </a>
                        </div>
                        <div class="col-md-6 col-sm-6 col-lg-6  col-xl-3 col-12">
                            {{-- return to receipt with centre --}}
                            <a href="{{ route('receipts', ['centre' => Auth::user()->department->id]) }}" target="_blank"
                                class="text-dark receipt-link">
                                <div class="info-box">
                                    <span class="info-box-icon bg-warning"><i class="fa-solid fa-cash-register"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text ">Payment Receipt</span>
                                        <span class="info-box-number" id="pareceipt">0</span>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6 col-sm-6 col-lg-6  col-xl-3 col-12">
                            {{-- return to receipt pending with centre --}}

                            <a id="receipts-pending-link"
                                href="{{ route('receipts_pending', ['centre' => Auth::user()->department->id]) }}"
                                target="_blank" class="text-dark">

                                <div class="info-box">
                                    <span class="info-box-icon bg-warning"><i
                                            class="fa-solid fa-hourglass-half"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text ">Pending Receipt</span>
                                        <span class="info-box-number" id="preceipt">0</span>
                                    </div>

                                </div>
                            </a>
                        </div>
                        <div class="col-md-6 col-sm-6 col-lg-6  col-xl-3 col-12">
                            <a href="{{ route('receipts', ['centre' => Auth::user()->department->id]) }}" target="_blank"
                                class="text-dark receipt-link">
                                <div class="info-box">
                                    <span class="info-box-icon bg-warning"><i
                                            class="fa-solid fa-hand-holding-dollar"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text ">Total Income</span>
                                        <span class="info-box-number" id="tincome">0</span>
                                    </div>

                                </div>
                            </a>
                        </div>

                    </div>
                    {{-- <div class="row">
                        <div class="col-md-6 col-sm-6 col-lg-6  col-xl-3 col-12">
                            <div class="info-box">
                                <span class="info-box-icon bg-danger"><i class="fa-solid fa-clipboard-question"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text ">เคสที่รับแจ้ง</span>
                                    <span class="info-box-number">10 เคส</span>
                                </div>

                            </div>

                        </div>


                        <div class="col-md-6 col-sm-6 col-lg-6  col-xl-3 col-12">
                            <div class="info-box">
                                <span class="info-box-icon bg-primary"><i class="fa-solid fa-clipboard-check"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text ">เคสที่ปิดเคสแล้ว</span>
                                    <span class="info-box-number">4 เคส</span>
                                </div>

                            </div>

                        </div>
                        <div class="col-md-6 col-sm-6 col-lg-6  col-xl-3 col-12">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning"><i class="fa-solid fa-shuffle"></i></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text ">เคสที่โอนสาย</span>
                                    <span class="info-box-number">6 เคส</span>
                                </div>

                            </div>

                        </div>
                        <div class="col-md-6 col-sm-6 col-lg-6  col-xl-3 col-12">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="fa-solid fa-star"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text ">คะแนนความพึงพอใจ</span>
                                    <span class="info-box-number">10 คะแนน</span>
                                </div>

                            </div>

                        </div>

                    </div> --}}

                    <div class="row">
                        <div class="col-md-4">

                            <div class="card card-primary" style="max-height: 485px">
                                <div class="card-header">
                                    <h3 class="card-title"><i class="fa-solid fa-chart-line"></i> Student By Level
                                    </h3>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" data-card-widget="maximize">
                                            <i class="fas fa-expand"></i>
                                        </button>
                                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                            <i class="fas fa-minus"></i>
                                        </button>

                                    </div>
                                </div>
                                <div class="card-body">
                                    <div id="chart_student_by_level" style="width: 100%; height: 450px;"></div>
                                </div>

                            </div>
                        </div>


                        <div class="col-md-8">

                            <div class="card card-primary" style="max-height: 485px">
                                <div class="card-header">
                                    <h3 class="card-title"><i class="fa-solid fa-chart-line"></i> Centre Student
                                        Status
                                    </h3>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" data-card-widget="maximize">
                                            <i class="fas fa-expand"></i>
                                        </button>
                                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                            <i class="fas fa-minus"></i>
                                        </button>

                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="chart">
                                        <div class="col-md-12">
                                            <div id="student_status" style="width: 100%; height: 450px;"></div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>

                    <div class="row">

                        <div class="col-md-12">

                            <div class="card card-primary" style="max-height: 485px">
                                <div class="card-header">
                                    <h3 class="card-title"><i class="fa-solid fa-chart-line"></i> Monthly Receipt
                                    </h3>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" data-card-widget="maximize">
                                            <i class="fas fa-expand"></i>
                                        </button>
                                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                            <i class="fas fa-minus"></i>
                                        </button>

                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="chart">
                                        <div class="col-md-12">
                                            <div id="receipt_sum_by_date" style="width: 100%; height: 450px;"></div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-8">

                            <div class="card card-primary" style="max-height: 455px">
                                <div class="card-header">
                                    <h3 class="card-title"><i class="fa-solid fa-chart-line"></i> Daily Study Student
                                    </h3>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" data-card-widget="maximize">
                                            <i class="fas fa-expand"></i>
                                        </button>
                                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                            <i class="fas fa-minus"></i>
                                        </button>

                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="chart">
                                        <div class="col-md-12">
                                            <div id="daily_study"></div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="col-md-4">

                            <div class="card card-primary" style="max-height: 455px">
                                <div class="card-header">
                                    <h3 class="card-title"><i class="fa-solid fa-chart-line"></i> Student By Centre
                                    </h3>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" data-card-widget="maximize">
                                            <i class="fas fa-expand"></i>
                                        </button>
                                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                            <i class="fas fa-minus"></i>
                                        </button>

                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="col-md-12">
                                        <div id="student_by_centre" style="width: 100%; height: 420px;"></div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>


                </div>

            </div>

        </div>

    </section>
</div>
@endsection

@section('script')
    @include('script')
    {{-- <script language="javascript" type="text/javascript">
        function generateRandomData(count) {
            const data = [];
            for (let i = 0; i < count; i++) {
                const newStudentValue = Math.floor(Math.random() * 100); // Generate a random value for "New Student" series
                const discontinuedStudentValue = newStudentValue *
                    0.5; // Set "Discontinued Student" as 50% of "New Student"
                data.push([discontinuedStudentValue, newStudentValue]);
            }
            return data;
        }

        function generateTimeLabels(count) {
            const labels = [];
            for (let i = 0; i < count; i++) {
                const startHour = i * 2;
                const endHour = startHour + 1;
                const label = `${startHour.toString().padStart(2, '0')}:00-${endHour.toString().padStart(2, '0')}:00`;
                labels.push(label);
            }
            return labels;
        }

        $(document).ready(function() {
            window.Apex.chart = {
                fontFamily: "Sarabun"
            };
            var options = {
                series: [{
                        name: 'New Student',
                        data: generateRandomData(5).map(item => item[1])
                    },
                    {
                        name: 'Discontinued Student',
                        data: generateRandomData(5).map(item => item[0])
                    },
                    /* {
                             name: 'Revenue',
                             data: [76, 85, 101, 98, 87, 105, 91, 114, 94]
                           }, {
                             name: 'Free Cash Flow',
                             data: [35, 41, 36, 26, 45, 48, 52, 53, 41]
                           } */
                ],
                chart: {
                    type: 'bar',
                    height: 350,

                },
                colors: ['#2E93fA', '#FF9800', '#546E7A', '#66DA26', '#E91E63', '#4ECDC4', '#C7F464', '#81D4FA',
                    '#A5978B', '#FD6A6A'
                ],
                title: {
                    //text: 'สถิติการเข้าชม แยกตามหน้า ประจำวันที่ 2023-06-30 - 2023-06-30',
                    margin: 50,
                    //offsetX: 50,
                    //offsetY: 100,
                },
                plotOptions: {
                    bar: {
                        dataLabels: {
                            position: 'top',
                            enabled: true,
                            textAnchor: 'start',
                            style: {
                                fontSize: '10pt',
                                colors: ['#000']
                            }
                        },
                        horizontal: false,
                        columnWidth: '75%',
                        endingShape: 'rounded'
                    },
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    show: true,
                    width: 2,
                    colors: ['transparent']
                },
                xaxis: {
                    labels: {
                        rotate: -30,
                        rotateAlways: true,
                        maxHeight: 300,
                        hideOverlappingLabels: false
                    },


                    categories: ['2019', '2020', '2021', '2022', '2023'],
                },
                yaxis: {

                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shade: 'light',
                        type: "horizontal",
                        shadeIntensity: 0.25,
                        gradientToColors: undefined,
                        inverseColors: true,
                        opacityFrom: 0.85,
                        opacityTo: 0.85,
                        stops: [50, 0, 100]
                    },
                    opacity: 1
                },
                tooltip: {
                    y: {
                        formatter: function(val) {
                            return " Amount " + val + "  Student"
                        }
                    }
                }
            };

            //var chart = new ApexCharts(document.querySelector("#chart"), options);
            //chart.render();

            var options_d = {
                series: [{
                        name: 'total Receipt',
                        data: generateRandomData(31).map(item => item[1])
                    },
                    /* {
                        name: 'สายที่ได้รับ',
                        data: generateRandomData(30).map(item => item[0])
                    }, */
                ],

                markers: {
                    size: 5,
                    colors: ["#FFFFFF"],
                    strokeColor: "#A5978B",
                    strokeWidth: 4
                },
                chart: {
                    type: 'area',
                    height: 380,
                    zoom: {
                        enabled: false
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'straight',
                    width: 4
                },
                colors: ['#E91E63', '#66DA26', '#546E7A', '#E91E63', '#FF9800', '#4ECDC4', '#C7F464', '#81D4FA',
                    '#A5978B', '#FD6A6A'
                ],
                title: {
                    //text: 'สถิติการเข้าชม รายวัน ประจำเดือน 2023-06',
                    align: 'left'
                },
                subtitle: {
                    //text: 'จำนวน',
                    align: 'left'
                },
                //labels: ['06-09','06-10','06-11','06-12','06-13','06-14','06-15','06-16','06-17','06-18','06-19','06-20','06-21','06-22','06-23','06-24','06-25','06-26','06-27','06-28','06-29'],
                /* xaxis: {
                   //type: 'datetime',
                }, */
                /*yaxis: {
                   opposite: true
                 }, */
                xaxis: {
                    labels: {
                        show: true,
                        rotate: -30,
                        rotateAlways: true,
                        maxHeight: 300,
                        //hideOverlappingLabels: false
                    },
                    categories: ['01', '02', '03', '04', '05', '06', '07', '08',
                        '09', '10', '11', '12', '13', '14', '15', '16', '17',
                        '18', '19', '20', '21', '22', '23', '24', '25', '26',
                        '27', '28', '29', '30', '31'
                    ],
                },
                legend: {
                    horizontalAlign: 'left'
                }
            };

            var chart_d = new ApexCharts(document.querySelector("#chart_date"), options_d);
            chart_d.render();


            var options_os = {
                series: generateRandomData(12).map(item => item[1]),
                chart: {
                    type: 'donut',
                    //width: 550,
                    height: 390,
                },
                colors: ['#2E93fA', '#66DA26', '#546E7A', '#E91E63', '#FF9800', '#4ECDC4', '#C7F464', '#81D4FA',
                    '#A5978B', '#FD6A6A'
                ],
                title: {
                    //text: 'OS ที่ดูมากที่สุด ประจำวันที่ 2023-06-30 - 2023-06-30',
                    align: 'center',
                    margin: 10,
                    offsetX: 0,
                    offsetY: 0,
                    floating: false,
                    style: {
                        fontSize: '14px',
                        fontWeight: 'bold',
                        //fontFamily: undefined,
                        color: '#263238'
                    },
                },
                labels: generateTimeLabels(12),
                responsive: [{
                    breakpoint: 200,
                    options: {
                        chart: {
                            width: 350,
                        },
                        legend: {
                            position: 'bottom'
                        }
                    }
                }]
            };

            //var chart_os = new ApexCharts(document.querySelector("#chart_os"), options_os);
            //chart_os.render();


            var options_c = {
                series: [10, 5, 18, 20, 50],
                chart: {
                    type: 'donut',
                    //width: 460,
                    height: 370,
                },
                colors: ['#4ECDC4', '#FF9800', '#2E93fA', '#66DA26', '#E91E63', '#546E7A', '#C7F464', '#81D4FA',
                    '#A5978B', '#FD6A6A'
                ],
                title: {
                    //text: 'ประเทศที่ดูมากที่สุด ประจำวันที่ 2023-06-30 - 2023-06-30',
                    align: 'left',
                    margin: 10,
                    offsetX: 0,
                    offsetY: 0,
                    floating: false,
                    style: {
                        fontSize: '14px',
                        fontWeight: 'bold',
                        //fontFamily: undefined,
                        color: '#263238'
                    },
                },

                labels: ['1', '2', '3', '4', '5'],
                responsive: [{
                    breakpoint: 200,
                    options: {
                        chart: {
                            width: 350,
                        },
                        legend: {
                            position: 'bottom'
                        }
                    }
                }]
            };

            //var chart_c = new ApexCharts(document.querySelector("#chart_c"), options_c);
            //chart_c.render();

            var pie4071 = echarts.init(document.getElementById("mainbc2_4071"));
            var option4071 = {
                title: {
                    show: false,
                    text: 'Referer of a Website',
                    subtext: 'Fake Data',
                    left: 'center'
                },
                tooltip: {
                    trigger: 'item'
                },
                toolbox: {
                    show: true,
                    feature: {
                        /* dataZoom: {
                            yAxisIndex: 'none'
                        },
                        dataView: {
                            readOnly: false
                        },
                        magicType: {
                            type: ['line', 'bar']
                        },
                        restore: {}, */
                        saveAsImage: {}
                    }
                },
                /* legend: {
                    orient: 'vertical',
                    left: 'left'
                }, */
                legend: {
                    top: '5%',
                    left: 'center'
                },
                series: [{
                    name: 'Student',
                    type: 'pie',
                    selectedMode: 'single',
                    radius: '60%',
                    center: ['50%', '45%'],
                    data: [{
                            value: 20,
                            name: '2019'
                        },
                        {
                            value: 70,
                            name: '2020'
                        },
                        {
                            value: 80,
                            name: '2021'
                        },
                        {
                            value: 152,
                            name: '2022'
                        },
                        {
                            value: 200,
                            name: '2023',
                            selected: true
                        }
                    ],
                    emphasis: {
                        itemStyle: {
                            shadowBlur: 10,
                            shadowOffsetX: 0,
                            shadowColor: 'rgba(0, 0, 0, 0.5)'
                        }
                    }
                }]
            };

            pie4071.setOption(option4071);
            window.addEventListener('resize', pie4071.resize);


            var datac = [];
            var timeIntervals = ['K1', 'K2', 'P1', 'P2',
                'P3',
                'P4', 'P5', 'P6'
            ];

            for (var i = 0; i < timeIntervals.length; i++) {
                var randomValue = Math.floor(Math.random() * 20); // Generate a random value between 0 and 999
                var dataPoint = {
                    value: randomValue,
                    name: timeIntervals[i]
                };
                datac.push(dataPoint);
            }
            var pie4072 = echarts.init(document.getElementById("mainbc2_4072"));

            option4072 = {
                tooltip: {
                    trigger: 'item'
                },
                toolbox: {
                    show: true,
                    feature: {
                        /* dataZoom: {
                            yAxisIndex: 'none'
                        },
                        dataView: {
                            readOnly: false
                        },
                        magicType: {
                            type: ['line', 'bar']
                        },
                        restore: {}, */
                        saveAsImage: {}
                    }
                },
                legend: {
                    show: true,
                    type: 'scroll',
                    orient: 'vertical',
                    right: 5,
                    top: 30,
                    bottom: 20,
                    data: datac.legendData
                },
                series: [{
                    name: 'Level',
                    type: 'pie',
                    radius: ['40%', '70%'],
                    center: ['35%', '40%'],
                    avoidLabelOverlap: true,
                    label: {
                        show: true,
                        position: 'inner',
                        fontSize: 10,
                        color: '#ffffff',
                        formatter(param) {
                            // correct the percentage
                            return /* param.name +  */ ' (' + param.percent * 2 + '%)';
                        }
                    },
                    color: ['#5470c6', '#91cc75', '#fac858', '#ee6666', '#73c0de', '#3ba272', '#fc8452',
                        '#9a60b4', '#ea7ccc', '#91c7ae',
                        '#fb7293',
                        '#96BFFF',
                        '#bda29a',
                    ],
                    emphasis: {
                        label: {
                            show: true,
                            fontSize: 20,
                            fontWeight: 'bold'
                        }
                    },
                    labelLine: {
                        show: true
                    },
                    data: datac
                }]
            };

            pie4072.setOption(option4072);
            window.addEventListener('resize', pie4072.resize);

        })
    </script> --}}
@endsection
