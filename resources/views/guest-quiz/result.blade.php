@extends('layouts.quiz_layout')
@section('title', 'ผลการทดสอบ')
@push('css')
<style>
    .result-card {
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        border: 1px solid rgba(249, 115, 22, 0.1);
        overflow: hidden;
    }
</style>
@endpush
@section('content')
<div style="background: linear-gradient(135deg, #f97316 0%, #fb923c 100%); padding: 40px 0; border-radius: 16px; margin-bottom: 32px; text-align: center;">
    <h1 style="font-size: 36px; font-weight: 800; color: #fff; margin: 0 0 12px 0;">📊 ผลการทดสอบ</h1>
    <p style="font-size: 18px; color: rgba(255,255,255,0.9); margin: 0;">สรุปผลการทำแบบทดสอบฟรี</p>
</div>

<!-- Score Summary -->
<div class="result-card" style="margin-bottom: 32px; padding: 32px;">
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 24px;">
        <div style="background: rgba(255,255,255,0.8); border-radius: 12px; padding: 24px; border: 1px solid rgba(249,115,22,0.1); text-align: center;">
            <p style="font-size: 14px; color: #64748b; margin: 0 0 8px 0;">📝 แบบทดสอบ</p>
            <p style="font-size: 18px; font-weight: 700; color: #1e293b; margin: 0;">{{ $guestTest->quiz->title }}</p>
        </div>
        <div style="background: rgba(255,255,255,0.8); border-radius: 12px; padding: 24px; border: 1px solid rgba(249,115,22,0.1); text-align: center;">
            <p style="font-size: 14px; color: #64748b; margin: 0 0 8px 0;">🏆 คะแนน</p>
            <p style="font-size: 28px; font-weight: 800; color: #1e293b; margin: 0;">
                {{ $guestTest->result }}/{{ $questionsCount }}
            </p>
            <p style="font-size: 14px; color: #64748b; margin: 4px 0 0 0;">
                ({{ $questionsCount > 0 ? number_format(($guestTest->result / $questionsCount) * 100, 1) : 0 }}%)
            </p>
        </div>
        <div style="background: rgba(255,255,255,0.8); border-radius: 12px; padding: 24px; border: 1px solid rgba(249,115,22,0.1); text-align: center;">
            <p style="font-size: 14px; color: #64748b; margin: 0 0 8px 0;">⏱️ เวลาที่ใช้</p>
            @php
                $h = intdiv($guestTest->time_spent, 3600);
                $m = intdiv($guestTest->time_spent % 3600, 60);
                $s = $guestTest->time_spent % 60;
            @endphp
            <p style="font-size: 18px; font-weight: 700; color: #1e293b; margin: 0;">
                {{ sprintf('%02d:%02d:%02d', $h, $m, $s) }}
            </p>
        </div>
    </div>
</div>

<!-- Question Results (ถูก/ผิด เท่านั้น ไม่แสดงเฉลย) -->
<div class="result-card" style="margin-bottom: 32px; padding: 32px;">
    <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 24px;">
        <div style="width: 6px; height: 32px; background: linear-gradient(135deg, #f97316 0%, #fb923c 100%); border-radius: 3px;"></div>
        <h2 style="font-size: 20px; font-weight: 700; color: #1e293b; margin: 0;">สรุปคำตอบ</h2>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(80px, 1fr)); gap: 12px;">
        @foreach($results as $result)
            <div style="text-align: center; padding: 16px 8px; border-radius: 12px; border: 2px solid {{ $result->correct ? '#22c55e' : '#ef4444' }}; background: {{ $result->correct ? 'rgba(34,197,94,0.05)' : 'rgba(239,68,68,0.05)' }};">
                <p style="font-size: 13px; color: #64748b; margin: 0 0 4px 0;">ข้อ {{ $loop->iteration }}</p>
                <span style="font-size: 24px;">{{ $result->correct ? '✅' : '❌' }}</span>
            </div>
        @endforeach
    </div>
</div>

<!-- Info Banner -->
<div style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border-radius: 12px; padding: 20px; border: 1px solid rgba(245,158,11,0.3); margin-bottom: 24px;">
    <p style="font-size: 14px; color: #92400e; margin: 0; display: flex; align-items: center; gap: 8px;">
        <span style="font-size: 20px;">💡</span>
        <span>เฉลยคำตอบจะแสดงเฉพาะนักเรียน eiMaths เท่านั้น สนใจสมัครเรียนติดต่อทีมงาน eiMaths</span>
    </p>
</div>

<!-- Actions -->
<div style="display: flex; justify-content: center; gap: 16px; flex-wrap: wrap;">
    <a href="{{ route('guest.quiz.home') }}" style="background: linear-gradient(135deg, #f97316 0%, #fb923c 100%); color: white; padding: 12px 32px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 14px;">
        ← กลับหน้าแบบทดสอบ
    </a>
    <form action="{{ route('guest.quiz.logout') }}" method="POST" style="display: inline;">
        @csrf
        <button type="submit" style="background: none; border: 1px solid #e2e8f0; padding: 12px 32px; border-radius: 8px; color: #64748b; font-size: 14px; cursor: pointer;">ออกจากระบบ</button>
    </form>
</div>
@endsection
