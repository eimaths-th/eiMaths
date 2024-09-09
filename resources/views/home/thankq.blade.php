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
