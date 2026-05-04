@extends('layouts.quiz_layout')
@section('title', 'eiMaths Free Quiz')
@section('content')
@push('scripts')
<script src="https://unpkg.com/lucide@0.263.0"></script>
<script>
  lucide.createIcons();
</script>
@endpush


<div style="background: linear-gradient(135deg, #f97316 0%, #fb923c 100%); padding: 30px; border-radius: 16px; margin-bottom: 32px; position: relative; overflow: hidden;">
    <div style="position: relative; z-index: 2; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <div>
            <h2 style="font-size: 24px; font-weight: 700; color: #fff; margin: 0 0 8px 0; display: flex; align-items: center; gap: 12px; flex-direction: row">
                <i data-lucide="user-check" style="color: #ffffff;" width="30" height="30"></i> 
                Welcome, {{ $guest->first_name }}</h2>
            <p style="font-size: 14px; color: rgba(255,255,255,0.9); margin: 0;">Grade {{ $guest->grade }} @if($guest->school) | {{ $guest->school }} @endif</p>
        </div>
        <div style="background: rgba(255,255,255,0.2); padding: 12px 20px; border-radius: 12px;">
            @if($daysRemaining > 0)
                <p style="font-size: 13px; color: rgba(255,255,255,0.8); margin: 0;">Time Remaining</p>
                <p style="font-size: 24px; font-weight: 800; color: #fff; margin: 4px 0 0 0;">{{ ceil($daysRemaining) }} Days</p>
            @else
                <p style="font-size: 14px; color: #fecaca; margin: 0; font-weight: 600;">⚠️ Last Day!</p>
            @endif
        </div>
    </div>
</div>

@if($freeQuizzesAll->count() > 0)
    <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 24px;">
        <div style="width: 6px; height: 32px; background: linear-gradient(135deg, #f97316 0%, #fb923c 100%); border-radius: 3px;"></div>
        <!-- <i data-lucide="pencil-ruler" style="color: #1e293b;" width="30" height="30"></i> -->
        <h3 style="font-size: 20px; font-weight: 700; color: #1e293b; margin: 0;">
             Free Quizzes</h3>
        <span style="background: rgba(249,115,22,0.1); color: #f97316; padding: 4px 12px; border-radius: 20px; font-size: 13px; font-weight: 600;">{{ $freeQuizzesAll->count() }} {{ $freeQuizzesAll->count() == 1 ? 'Set' : 'Sets' }}</span>
    </div>

    @foreach($freeQuizzes as $level => $quizzes)
        <div style="margin-bottom: 24px;">
            <h4 style="font-size: 16px; font-weight: 600; color: #64748b; margin: 0 0 16px 0;">
                <i data-lucide="layer-forward" style="color: #1e293b;" width="20" height="20"></i> Level: {{ $level }}</h4>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px;">
                @foreach($quizzes as $quiz)
                    <a href="{{ route('guest.quiz.show', ['quiz' => $quiz->slug]) }}" style="text-decoration: none;">
                        <div style="background: rgba(255,255,255,0.9); border-radius: 16px; padding: 24px; border: 2px solid rgba(249,115,22,0.1); box-shadow: 0 4px 12px rgba(0,0,0,0.05); transition: all 0.3s ease;"
                             onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 8px 25px rgba(249,115,22,0.15)';"
                             onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.05)';">
                            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
                                <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #f97316 0%, #fb923c 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 20px;">
                                    <i data-lucide="book" style="color: #ffffff;" width="25" height="25"></i></div>
                                <div>
                                    <h5 style="font-size: 16px; font-weight: 700; color: #1e293b; margin: 0;">{{ $quiz->title }}</h5>
                                    <p style="font-size: 13px; color: #64748b; margin: 4px 0 0 0;">{{ $quiz->questions_count }} Questions</p>
                                </div>
                            </div>
                            <div style="display: flex; align-items: center; justify-content: space-between;">
                                <span style="background: rgba(249,115,22,0.1); color: #f97316; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">
                                    Free</span>
                                <span style="color: #f97316; font-size: 13px; font-weight: 600;">Start →</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endforeach
@else
    <div style="text-align: center; padding: 60px 20px;">
        <div style="font-size: 64px; margin-bottom: 16px;">📭</div>
        <h3 style="font-size: 20px; font-weight: 700; color: #1e293b;">No Free Quizzes Available</h3>
        <p style="font-size: 14px; color: #64748b;">Please check back later.</p>
    </div>
@endif

<div style="text-align: center; margin-top: 32px; padding-top: 24px; border-top: 1px solid rgba(0,0,0,0.05);">
    <form action="{{ route('guest.quiz.logout') }}" method="POST" style="display: inline;">
        @csrf
        <button type="submit" style="background: none; border: 1px solid #e2e8f0; padding: 10px 24px; border-radius: 8px; color: #64748b; font-size: 14px; cursor: pointer;">Logout</button>
    </form>
</div>
@endsection
