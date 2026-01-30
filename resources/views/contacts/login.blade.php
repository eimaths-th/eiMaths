@extends('layouts.default', [
	'paceTop' => false,
	'appSidebarHide' => true,
	'appHeaderHide' => true,
	'appContentClass' => 'p-0'
])

@section('title', 'Login Page')

@section('content')
	<!-- BEGIN login -->
	<div class="login login-with-news-feed">
		<!-- BEGIN news-feed -->
		<div class="news-feed">
			<div class="news-image" style="background-image: url(/images/student_site.jpg)"></div>
			<div class="news-caption">
				<h4 class="caption-title"><b>eiMaths</b> Student site</h4>
				<p>
					For Student of eiMaths Thailand
				</p>
			</div>
		</div>
		<!-- END news-feed -->

		<!-- BEGIN login-container -->
		<div class="login-container">
			<!-- BEGIN login-header -->
			<div class="login-header mb-30px">
				<div class="brand">
					<div class="d-flex align-items-center">
						{{-- <span class="logo text-warning"></span> --}}
                        <img src="{{ asset('images/Eimaths-Logo-Without-Halo.png') }}"  width="120" height="55" alt="Logo">
						<b class="text-warning">Student</b>&nbsp;login
					</div>
					<small>For Student of eiMaths Thailand</small>
				</div>
				<div class="icon">
					<i class="fa fa-sign-in-alt"></i>
				</div>
			</div>
			<!-- END login-header -->

			<!-- BEGIN login-content -->
			<div class="login-content">
				<form action="{{ route('student.check') }}" method="POST" class="fs-13px">
                    @csrf
					@if (isset($message))
                        <div class="alert alert-danger" role="alert">
                            {{ $message ?? '' }}
                        </div>
                    @endif
					<div class="form-floating mb-15px">
						<input type="text" class="form-control h-45px fs-13px" placeholder="Code Student" id="code" name="code"/>
						<label for="code" class="d-flex align-items-center fs-13px text-gray-600">Code Student</label>
					</div>
					<div class="form-floating mb-15px">
						<input type="password" class="form-control h-45px fs-13px" placeholder="Password" id="password" name="password"/>
						<label for="password" class="d-flex align-items-center fs-13px text-gray-600">Password</label>
					</div>
					<div class="form-check mb-30px">
						<input class="form-check-input" type="checkbox" value="1" id="rememberMe" name="rememberMe"/>
						<label class="form-check-label" for="rememberMe">
							Remember Me
						</label>
					</div>
					<div class="mb-15px">
						<button type="submit" class="btn btn-warning d-block h-45px w-100 btn-lg fs-14px">Sign in</button>
					</div>
					
					{{-- <div class="mb-40px pb-40px text-dark">
						Not a member yet? Click <a href="/register/v3" class="text-primary">here</a> to register.
					</div> --}}
					<hr class="bg-gray-600 opacity-2" />
					<div class="text-gray-600 text-center text-gray-500-darker mb-0">
						&copy; Student of eiMaths Thailand {{ date('Y') }}
					</div>
				</form>
			</div>
			<!-- END login-content -->
		</div>
		<!-- END login-container -->
	</div>
	<!-- END login -->
@endsection
