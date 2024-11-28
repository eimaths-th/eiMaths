@extends('layouts.form')

@section('style')
    <style>
        body {
            background-color: #ff964a;
        }

        .form_header {
            color: #ff7d20;
        }

        .form_sub_header {
            color: #ff964a;
        }

        hr {
            margin-top: 1rem;
            margin-bottom: 1rem;
            border: 0;
            border-top: 1px solid #112d53;
        }


        .div_center {
            margin: auto;

        }

        .form-sub-label {
            color: #224779;
        }

        .form-sub-label {
            font-size: .75em;
            margin-top: 11px;
            margin-left: 2px;
            display: block;
            word-break: break-word;
        }



        .bg-light2 {
            background-color: #fff9e4;
        }

        .form_sub_header a {
            text-decoration: none;
        }

        .form_sub_header a:hover {
            text-decoration: none;
        }
    </style>
@endsection


@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-9">
                <div class="card bg-light2 mb-3 ">

                    <div class="card-body">
                        <br>
                        <div align="center"><img src="{{ asset('images/logo.png') }}" alt="..." height="200">
                        </div>
                        <div class="text-center">
                            <h1 class="card-title form_header">Thank You!</h1>
                            <p class="card-text form_sub_header"><b>Your submission has been received.</b></p>
                            <p class="card-text form_sub_header"><b>Please contact us</b></p>


                        </div>
                        <div class="row">
                            <!-- First Column -->
                            {{-- <div class="col-lg-6">

                                <h4 class="card-text form_sub_header">eiMaths TH - Ratchapruek</h4>

                                <a href="tel:+66616208666" class="card-text form_sub_header">
                                    <p class="card-text form_sub_header">
                                        <i class="fas fa-phone-alt"></i>
                                        <b> +666-1620-8666</b>
                                    </p>
                                </a>

                                <a href="https://lin.ee/TqNbyqr" target="_blank" class="card-text form_sub_header">
                                    <p class="card-text form_sub_header">
                                        <i class="fab fa-line"></i>
                                        <b> @eiMaths</b>
                                    </p>
                                </a>
                                <a href="https://www.facebook.com/eimaths.th" target="_blank"
                                    class="card-text form_sub_header">
                                    <p class="card-text form_sub_header">
                                        <i class="fab fa-facebook"></i>
                                        <b>eiMaths TH</b>
                                    </p>

                                </a>
                            </div> --}}

                            <div class="col-lg-6">
                                <h4 class="card-text form_sub_header text-center">eiMaths TH - Ratchapruek</h4>

                                <p class="card-text form_sub_header text-center">
                                    <i class="fas fa-phone-alt"></i>
                                    <b><a href="tel:+66616208666" class="form_sub_header"> +666-1620-8666</a></b>
                                </p>

                                <p class="card-text form_sub_header text-center">
                                    <i class="fab fa-line"></i>
                                    <b><a href="https://lin.ee/TqNbyqr" target="_blank" class="form_sub_header">
                                            @eiMaths</a></b>
                                </p>

                                <p class="card-text form_sub_header text-center">
                                    <i class="fab fa-facebook"></i>
                                    <b><a href="https://www.facebook.com/eimaths.th" target="_blank"
                                            class="form_sub_header">eiMaths TH</a></b>
                                </p>
                            </div>
                            <!-- Third Column -->
                            <div class="col-lg-6">
                                <h4 class="card-text form_sub_header text-center">eiMaths TH - Bangkae</h4>

                                <p class="card-text form_sub_header text-center">
                                    <i class="fas fa-phone-alt"></i>
                                    <b><a href="tel:+66616208666" class="form_sub_header"> +6693-258-5897</a></b>
                                </p>

                                <p class="card-text form_sub_header text-center">
                                    <i class="fab fa-line"></i>
                                    <b><a href="https://lin.ee/SFpwGwU" target="_blank" class="form_sub_header">
                                        @eiMathsBangkae</a></b>
                                </p>

                                <p class="card-text form_sub_header text-center">
                                    <i class="fab fa-facebook"></i>
                                    <b><a href="https://www.facebook.com/profile.php?id=61551963011804" target="_blank"
                                            class="form_sub_header">eiMaths - Bangkae</a></b>
                                </p>
                            </div>
                            {{-- <div class="col-lg-6">
                                <div class="contact-info">
                                    <h2>eiMaths TH - Bangkae</h2>
                                </div>
                                <div class="contact-info">
                                    <a href="tel:+66932585897">
                                        <i class="fas fa-phone-alt"></i> +6693-258-5897
                                    </a>
                                </div>
                                <div class="contact-info">
                                    <a href="https://lin.ee/SFpwGwU" target="_blank">
                                        <i class="fab fa-line"></i> @eiMathsBangkae
                                    </a>
                                </div>
                                <div class="contact-info">
                                    <a href="https://www.facebook.com/eimaths.th">
                                        <i class="fab fa-facebook"></i> eiMaths - Bangkae
                                    </a>
                                </div>
                            </div> --}}
                        </div>

                        <br>

                    </div>
                    <div class="card-footer text-center">
                        <br><br>
                        <a class="btn btn-lg btn-primary" href="{{ route('home3') }}">Go Back To Quiz</a><br><br>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
