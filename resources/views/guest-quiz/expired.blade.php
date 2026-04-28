@extends('layouts.default', [
	'paceTop' => false,
	'appSidebarHide' => true,
	'appHeaderHide' => true,
	'appContentClass' => 'p-0'
])

@section('title', 'หมดอายุการใช้งาน')

@section('content')
<div style="min-height: 100vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); padding: 20px;">
    <div style="max-width: 500px; width: 100%; text-align: center;">
        <div style="background: white; border-radius: 24px; padding: 48px 32px; box-shadow: 0 8px 32px rgba(0,0,0,0.1); border: 1px solid rgba(239,68,68,0.1);">
            <!-- Icon -->
            <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px; font-size: 36px;">
                ⏰
            </div>

            <h1 style="font-size: 28px; font-weight: 800; color: #1e293b; margin: 0 0 12px 0;">หมดอายุการใช้งาน</h1>
            <p style="font-size: 16px; color: #64748b; margin: 0 0 32px 0; line-height: 1.6;">
                สิทธิ์ทดสอบฟรี 7 วันของคุณหมดอายุแล้ว<br>
                ข้อมูลผลสอบถูกลบเรียบร้อยแล้ว
            </p>

            <!-- Contact Info -->
            <div style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border-radius: 16px; padding: 24px; margin-bottom: 32px; border: 1px solid rgba(245,158,11,0.3);">
                <h3 style="font-size: 16px; font-weight: 700; color: #92400e; margin: 0 0 12px 0;">
                    📞 ติดต่อทีมงาน eiMaths
                </h3>
                <p style="font-size: 14px; color: #92400e; margin: 0; line-height: 1.8;">
                    สนใจสมัครเรียนเพื่อเข้าถึงแบบทดสอบทั้งหมด<br>
                    พร้อมเฉลยอย่างละเอียด
                </p>
                <div style="margin-top: 16px; display: flex; flex-direction: column; gap: 8px; align-items: center;">
                    <span style="font-size: 14px; color: #78350f;">📧 Email: info@eimaths.com</span>
                    <span style="font-size: 14px; color: #78350f;">📱 LINE: @eimaths</span>
                </div>
            </div>

            <a href="{{ route('student.login') }}" style="display: inline-block; background: linear-gradient(135deg, #f97316 0%, #fb923c 100%); color: white; padding: 14px 40px; border-radius: 12px; text-decoration: none; font-weight: 600; font-size: 16px;">
                กลับหน้าเข้าสู่ระบบ
            </a>
        </div>
    </div>
</div>
@endsection
