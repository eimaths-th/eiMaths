@extends('layouts.default', [
	'paceTop' => false,
	'appSidebarHide' => true,
	'appHeaderHide' => true,
	'appContentClass' => 'p-0'
])

@section('title', 'Subscription Expired')



@section('content')
<div style="min-height: 100vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); padding: 20px;">
    <div style="max-width: 500px; width: 100%; text-align: center;">
        <div style="background: white; border-radius: 24px; padding: 48px 32px; box-shadow: 0 8px 32px rgba(0,0,0,0.1); border: 1px solid rgba(239,68,68,0.1);">
            <!-- Icon -->
            <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px; font-size: 36px;">
                <i data-lucide="clock" style="color: white;" width="40" height="40"></i>
            </div>

            <h1 style="font-size: 28px; font-weight: 800; color: #1e293b; margin: 0 0 12px 0;">Subscription Expired</h1>
            <p style="font-size: 16px; color: #64748b; margin: 0 0 32px 0; line-height: 1.6;">
                Your 7-day free trial has expired.<br>
                All test data has been deleted.
            </p>

            <!-- Contact Info -->
            <div style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border-radius: 16px; padding: 24px; margin-bottom: 32px; border: 1px solid rgba(245,158,11,0.3);">
                <h3 style="font-size: 16px; font-weight: 700; color: #92400e; margin: 0 0 12px 0;">
                    <i data-lucide="phone"></i> Contact eiMaths Team
                </h3>
                <p style="font-size: 14px; color: #92400e; margin: 0; line-height: 1.8;">
                    Interested in enrolling for full access to all quizzes<br>
                    with detailed answer explanations?
                </p>
                <!-- <button style="background-color: #f97316; color: white; padding: 12px 20px; border: none; border-radius: 8px; cursor: pointer;" onclick="window.location.href='https://eimaths-th.com/contact'">
                    <i data-lucide="message-circle"></i> ติดต่อสมัครเรียน
                </button> -->
                <div style="margin-top: 16px; display: flex; flex-direction: column; gap: 12px;">
                    <div style="display: flex; align-items: center; padding: 10px 16px; background: rgba(255, 255, 255, 0.6); border-radius: 10px; transition: all 0.3s ease;" onmouseover="this.style.background='rgba(255, 255, 255, 0.9)';" onmouseout="this.style.background='rgba(255, 255, 255, 0.6)';">
                        <i class="fas fa-phone-alt" style="color: #ea580c; margin-right: 12px; font-size: 18px; text-align: center;"></i>
                        <a href="tel:+66616208666" style="color: #92400e; text-decoration: none; font-weight: 600; font-size: 15px; transition: color 0.3s ease;" onmouseover="this.style.color='#ea580c';" onmouseout="this.style.color='#92400e';">+666-1620-8666</a>
                    </div>
                    <div style="display: flex; align-items: center; padding: 10px 16px; background: rgba(255, 255, 255, 0.6); border-radius: 10px; transition: all 0.3s ease;" onmouseover="this.style.background='rgba(255, 255, 255, 0.9)';" onmouseout="this.style.background='rgba(255, 255, 255, 0.6)';">
                        <i class="fab fa-line" style="color: #00c300; margin-right: 12px; font-size: 20px; text-align: center;"></i>
                        <a href="https://lin.ee/TqNbyqr" target="_blank" style="color: #92400e; text-decoration: none; font-weight: 600; font-size: 15px; transition: color 0.3s ease;" onmouseover="this.style.color='#00c300';" onmouseout="this.style.color='#92400e';">@eimaths-th</a>
                    </div>
                    <div style="display: flex; align-items: center; padding: 10px 16px; background: rgba(255, 255, 255, 0.6); border-radius: 10px; transition: all 0.3s ease;" onmouseover="this.style.background='rgba(255, 255, 255, 0.9)';" onmouseout="this.style.background='rgba(255, 255, 255, 0.6)';">
                        <i class="fab fa-facebook" style="color: #1877f2; margin-right: 12px; font-size: 20px; text-align: center;"></i>
                        <a href="https://www.facebook.com/eimaths.th" target="_blank" style="color: #92400e; text-decoration: none; font-weight: 600; font-size: 15px; transition: color 0.3s ease;" onmouseover="this.style.color='#1877f2';" onmouseout="this.style.color='#92400e';">eiMaths TH</a>
                    </div>
                </div>
            </div>

            <a href="{{ route('student.login') }}" style="display: inline-block; background: linear-gradient(135deg, #f97316 0%, #fb923c 100%); color: white; padding: 14px 40px; border-radius: 12px; text-decoration: none; font-weight: 600; font-size: 16px;">
                Back to Login
            </a>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script src="https://unpkg.com/lucide@latest"></script>
<script>
    lucide.createIcons();
</script>
@endpush
