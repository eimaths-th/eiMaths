@extends('layouts.quiz_layout')

@section('title', 'Home Page')

@push('css')
    <style>
        .img-container {
            display: flex;
            justify-content: flex-start;
            align-items: flex-start;
            width: 100%;
            height: auto;
        }

        .img-custom {
            max-height: 100px;
            max-width: 100%;
            height: auto;
            width: auto;
            display: inline-block;
            vertical-align: middle;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease;
        }

        .img-custom:hover {
            transform: scale(1.05);
        }

        li span {
            display: inline-block;
            vertical-align: middle;
        }

        .result-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(249, 115, 22, 0.1);
            overflow: hidden;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .result-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
        }

        .img-hover-preview {
            position: fixed;
            top: 0;
            left: 0;
            display: none;
            pointer-events: none;
            z-index: 9999;
            max-width: 60vw;
            max-height: 70vh;
            border-radius: 10px;
            background: #fff;
            border: 1px solid rgba(0,0,0,0.08);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            padding: 8px;
        }
    </style>
@endpush
@push('scripts')
<script>
    (function(){
        const OFFSET_X = 18;
        const OFFSET_Y = 18;

        const preview = document.createElement('img');
        preview.className = 'img-hover-preview';
        document.body.appendChild(preview);

        function positionPreview(e) {
            const vw = window.innerWidth;
            const vh = window.innerHeight;
            const rect = { w: preview.naturalWidth, h: preview.naturalHeight };

            let scale = 1;
            const maxW = vw * 0.6; // 60vw
            const maxH = vh * 0.7; // 70vh
            if (rect.w > maxW || rect.h > maxH) {
                scale = Math.min(maxW / rect.w, maxH / rect.h);
            }

            preview.style.width = rect.w ? (rect.w * scale) + 'px' : '';
            preview.style.height = rect.h ? (rect.h * scale) + 'px' : '';

            let x = e.clientX + OFFSET_X;
            let y = e.clientY + OFFSET_Y;

            const pw = preview.getBoundingClientRect().width;
            const ph = preview.getBoundingClientRect().height;

            if (x + pw > vw - 10) x = e.clientX - pw - OFFSET_X;
            if (y + ph > vh - 10) y = e.clientY - ph - OFFSET_Y;

            preview.style.transform = `translate(${x}px, ${y}px)`;
        }

        function attachPreview(el) {
            el.addEventListener('mouseenter', (e) => {
                preview.src = el.getAttribute('src');
                preview.style.display = 'block';
                positionPreview(e);
            });
            el.addEventListener('mousemove', positionPreview);
            el.addEventListener('mouseleave', () => {
                preview.style.display = 'none';
            });
        }

        document.addEventListener('DOMContentLoaded', function(){
            document.querySelectorAll('.answer-explain-thumb').forEach(attachPreview);
        });
    })();
</script>
@endpush
@section('content')
    <!-- Results Header Section -->
    <div
        style="background: linear-gradient(135deg, #f97316 0%, #fb923c 100%); padding: 40px 0; position: relative; overflow: hidden;">
        <!-- Header Background Pattern -->
        <div
            style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; opacity: 0.1; background-image: radial-gradient(circle at 20px 20px, #ffffff 2px, transparent 0); background-size: 40px 40px;">
        </div>

        <div style="max-width: 1280px; margin: 0 auto; padding: 0 24px; position: relative; z-index: 2;">
            <div style="text-align: center;">
                <h1
                    style="font-size: 36px; font-weight: 800; color: #ffffff; margin: 0 0 12px 0; text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
                    📊 ผลการทดสอบ
                </h1>
                <p style="font-size: 18px; color: rgba(255, 255, 255, 0.9); margin: 0; font-weight: 500;">
                    รายละเอียดผลการทำแบบทดสอบของคุณ
                </p>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div style="padding: 40px 0; background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); min-height: 100vh;">
        <div style="max-width: 1280px; margin: 0 auto; padding: 0 24px;">
            <!-- Test Summary Card -->
            <div class="result-card" style="margin-bottom: 32px; position: relative;">
                <!-- Card Decoration -->
                <div
                    style="position: absolute; top: 0; right: 0; width: 150px; height: 150px; background: radial-gradient(circle, rgba(249, 115, 22, 0.08) 0%, transparent 70%); border-radius: 50%;">
                </div>

                <div style="padding: 32px; position: relative; z-index: 2;">
                    <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 24px;">
                        <div
                            style="width: 6px; height: 40px; background: linear-gradient(135deg, #f97316 0%, #fb923c 100%); border-radius: 3px; box-shadow: 0 2px 8px rgba(249, 115, 22, 0.3);">
                        </div>
                        <h2 style="font-size: 24px; font-weight: 700; color: #1e293b; margin: 0;">สรุปผลการทดสอบ</h2>
                    </div>

                    <!-- Test Information Grid -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px;">
                        <!-- Quiz Info Card -->
                        <div
                            style="background: rgba(255, 255, 255, 0.8); border-radius: 12px; padding: 24px; border: 1px solid rgba(249, 115, 22, 0.1); backdrop-filter: blur(5px);">
                            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
                                <div
                                    style="width: 40px; height: 40px; background: linear-gradient(135deg, #f97316 0%, #fb923c 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-size: 18px; font-weight: bold;">
                                    📝</div>
                                <div>
                                    <h3
                                        style="font-size: 16px; font-weight: 600; color: #64748b; margin: 0; text-transform: uppercase; letter-spacing: 0.5px;">
                                        แบบทดสอบ</h3>
                                    <p style="font-size: 18px; font-weight: 700; color: #1e293b; margin: 4px 0 0 0;">
                                        {{ $test->quiz->title }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- User Info Card -->
                        <div
                            style="background: rgba(255, 255, 255, 0.8); border-radius: 12px; padding: 24px; border: 1px solid rgba(249, 115, 22, 0.1); backdrop-filter: blur(5px);">
                            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
                                <div
                                    style="width: 40px; height: 40px; background: linear-gradient(135deg, #10b981 0%, #34d399 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-size: 18px; font-weight: bold;">
                                    👤</div>
                                <div>
                                    <h3
                                        style="font-size: 16px; font-weight: 600; color: #64748b; margin: 0; text-transform: uppercase; letter-spacing: 0.5px;">
                                        ผู้ทำแบบทดสอบ</h3>
                                    <p style="font-size: 18px; font-weight: 700; color: #1e293b; margin: 4px 0 0 0;">
                                        @php
                                            if ($test->type_user === 'user' && $test->user) {
                                                $testUser = $test->user;
                                            } elseif ($test->type_user === 'student' && $test->student) {
                                                $testUser = $test->student;
                                            } else {
                                                $testUser = null;
                                            }
                                        @endphp
                                        @if ($testUser)
                                            {{ $testUser->nickname ?? $testUser->name }}
                                            <br><span
                                                style="font-size: 14px; color: #64748b;">({{ $testUser->email ?? $testUser->name }})</span>
                                        @else
                                            <span style="color: #ef4444;">User not found</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Date Info Card -->
                        <div
                            style="background: rgba(255, 255, 255, 0.8); border-radius: 12px; padding: 24px; border: 1px solid rgba(249, 115, 22, 0.1); backdrop-filter: blur(5px);">
                            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
                                <div
                                    style="width: 40px; height: 40px; background: linear-gradient(135deg, #8b5cf6 0%, #a78bfa 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-size: 18px; font-weight: bold;">
                                    📅</div>
                                <div>
                                    <h3
                                        style="font-size: 16px; font-weight: 600; color: #64748b; margin: 0; text-transform: uppercase; letter-spacing: 0.5px;">
                                        วันที่ทำแบบทดสอบ</h3>
                                    <p style="font-size: 18px; font-weight: 700; color: #1e293b; margin: 4px 0 0 0;">
                                        {{ $test->created_at->format('D d/m/Y, h:i A') ?? '' }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Score Info Card -->
                        <div
                            style="background: rgba(255, 255, 255, 0.8); border-radius: 12px; padding: 24px; border: 1px solid rgba(249, 115, 22, 0.1); backdrop-filter: blur(5px);">
                            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
                                <div
                                    style="width: 40px; height: 40px; background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-size: 18px; font-weight: bold;">
                                    🏆</div>
                                <div>
                                    <h3
                                        style="font-size: 16px; font-weight: 600; color: #64748b; margin: 0; text-transform: uppercase; letter-spacing: 0.5px;">
                                        คะแนน</h3>
                                    <p style="font-size: 18px; font-weight: 700; color: #1e293b; margin: 4px 0 0 0;">
                                        {{ $test->result ?? 0 }}/{{ $test->quiz->questions->count() }}
                                        <span style="font-size: 16px; color: #64748b; font-weight: 500;">
                                            ({{ number_format(($test->result / $test->quiz->questions->count()) * 100, 2) }}%)
                                        </span>
                                        @if ($test->time_spent)
                                            @php
                                                $hour = intdiv($test->time_spent, 3600);
                                                $minutes = intdiv($test->time_spent, 60);
                                                $seconds = $test->time_spent % 60;
                                            @endphp
                                            <br><span style="font-size: 14px; color: #64748b;">
                                                เวลาที่ใช้:
                                                {{ sprintf('%02d', $hour) }}:{{ sprintf('%02d', $minutes) }}:{{ sprintf('%02d', $seconds) }}
                                            </span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {{-- @isset($leaderboard)
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 pb-12">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h6 class="text-xl font-bold">Leaderboard</h6>

                        <table class="table mt-4 w-full table-view">
                            <thead>
                                <th class="text-left">Rank</th>
                                <th class="text-left">Username</th>
                                <th class="text-left">Results</th>
                            </thead>
                            <tbody class="bg-white">
                                @foreach ($leaderboard as $test)
                                    <tr @class([
                                        'bg-gray-100' => auth()->user()->name == $test->user->name,
                                    ])>
                                        <td class="w-9">{{ $loop->iteration }}</td>
                                        <td class="w-1/2">{{ $test->user->name }}</td>
                                        <td>{{ $test->result }} / {{ $questions_count }} (time: {{ $test->time_spent }} sec)
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endisset --}}
            <br>

            {{-- @php
            dd($results);
        @endphp --}}
            <!-- Questions Results Section -->
            <div class="result-card" style="margin-bottom: 32px;">
                <div style="padding: 32px; position: relative; z-index: 2;">
                    <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 32px;">
                        <div
                            style="width: 6px; height: 40px; background: linear-gradient(135deg, #f97316 0%, #fb923c 100%); border-radius: 3px; box-shadow: 0 2px 8px rgba(249, 115, 22, 0.3);">
                        </div>
                        <h2 style="font-size: 24px; font-weight: 700; color: #1e293b; margin: 0;">รายละเอียดคำตอบ</h2>
                    </div>

                    <!-- Questions Grid -->
                    <div style="display: grid; gap: 24px;">
                        @foreach ($results as $result)
                            <!-- Question Card -->
                            <div
                                style="background: rgba(255, 255, 255, 0.9); border-radius: 16px; padding: 24px; border: 2px solid {{ $result->correct ? 'rgba(34, 197, 94, 0.2)' : 'rgba(239, 68, 68, 0.2)' }}; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05); position: relative; overflow: hidden; display: flex; flex-direction: column; min-height: 250px;">
                                <!-- Status Indicator -->
                                <div
                                    style="position: absolute; top: 0; right: 0; width: 60px; height: 60px; background: {{ $result->correct ? 'linear-gradient(135deg, #22c55e 0%, #16a34a 100%)' : 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)' }}; border-radius: 0 16px 0 50px; display: flex; align-items: center; justify-content: center; color: white; font-size: 20px;">
                                    {{ $result->correct ? '✓' : '✗' }}
                                </div>
                                <!-- Question Header -->
                                <div
                                    style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-right: 70px;">
                                    <div>
                                        <h3 style="font-size: 20px; font-weight: 700; color: #1e293b; margin: 0 0 8px 0;">
                                            คำถามที่ {{ $loop->iteration }}
                                        </h3>
                                        @if ($result->question->type === 'fraction')
                                            <span
                                                style="background: linear-gradient(135deg, #f97316 0%, #fb923c 100%); color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                                                เศษส่วน
                                            </span>
                                        @elseif ($result->question->type === 'written')
                                            <span
                                                style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                                                คำตอบเขียน
                                            </span>
                                        @elseif ($result->question->type === 'image')
                                            <span
                                                style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                                                รูปภาพ
                                            </span>
                                        @else
                                            <span
                                                style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                                                ตัวเลือก
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <!-- Question Text -->
                                <div style="margin-bottom: 24px; flex: 1;">
                                    @if ($result->question->type === 'fraction')
                                        {{-- <td>{!! nl2br($result->question->text) !!}</td> --}}
                                        <p style="font-size: 18px; font-weight: 500; color: #64748b; margin: 0;">
                                            {{ replaceMixedFractions($result->question->text) }}</p>
                                    @else
                                        <p style="font-size: 18px; font-weight: 500; color: #64748b; margin: 0;">
                                            {!! nl2br(replaceMixedFractions($result->question->text)) !!}</p>
                                    @endif
                                </div>
                                <!-- Answer Section -->
                                <div style="display: grid; grid-template-columns: 1fr; gap: 16px; margin-top: auto;">
                                    @php
                                        $title = '';
                                        $type = $result->question->type;
                                        if ($type === 'options' || $type === 'image') {
                                            # code...
                                            $title = 'Options';
                                        } elseif ($type === 'written') {
                                            # code...
                                            $title = 'Written';
                                        } elseif ($type === 'fraction') {
                                            # code...
                                            $title = 'Fraction';
                                        } else {
                                            # code...
                                            $title = 'Title is not found';
                                        }
                                    @endphp
                                    <h4
                                        style="font-size: 16px; font-weight: 600; color: #64748b; margin: 0 0 8px 0; text-transform: uppercase; letter-spacing: 0.5px;">
                                        {{ $title }}</h4>
                                    @if ($type === 'options')
                                        <div style="display: grid; gap: 12px;">
                                            @foreach ($result->question->options as $option)
                                                <div
                                                    style="padding: 16px; border-radius: 12px; border: 2px solid {{ $result->option_id == $option->id ? ($option->correct ? '#22c55e' : '#ef4444') : ($option->correct ? '#22c55e' : '#e2e8f0') }}; background: {{ $option->correct ? 'rgba(34, 197, 94, 0.05)' : ($result->option_id == $option->id ? 'rgba(239, 68, 68, 0.05)' : 'rgba(255, 255, 255, 0.8)') }}; position: relative;">
                                                    <div style="display: flex; align-items: center; gap: 12px;">
                                                        <div
                                                            style="width: 32px; height: 32px; border-radius: 50%; background: {{ $option->correct ? 'linear-gradient(135deg, #22c55e 0%, #16a34a 100%)' : ($result->option_id == $option->id ? 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)' : 'linear-gradient(135deg, #64748b 0%, #475569 100%)') }}; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 14px;">
                                                            {{ chr(65 + $loop->index) }}
                                                        </div>
                                                        <div style="flex: 1;">
                                                            <p
                                                                style="font-size: 16px; font-weight: 500; color: #1e293b; margin: 0;">
                                                                {{ replaceMixedFractions($option->text) }}</p>
                                                            <div style="margin-top: 4px; display: flex; gap: 8px;">
                                                                @if ($option->correct == 1)
                                                                    <span
                                                                        style="background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); color: white; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 600;">คำตอบที่ถูก</span>
                                                                @endif
                                                                @if ($result->option_id == $option->id)
                                                                    <span
                                                                        style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); color: white; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 600;">คำตอบของคุณ</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        @if ($option->correct == 1)
                                                            <div style="color: #22c55e; font-size: 20px;">✓</div>
                                                        @elseif ($result->option_id == $option->id && !$option->correct)
                                                            <div style="color: #ef4444; font-size: 20px;">✗</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                            @if (is_null($result->option_id))
                                                <div
                                                    style="padding: 16px; border-radius: 12px; border: 2px dashed #fbbf24; background: rgba(251, 191, 36, 0.05); text-align: center;">
                                                    <span
                                                        style="font-weight: 600; color: #f59e0b; font-style: italic;">ไม่ได้ตอบคำถามนี้</span>
                                                </div>
                                            @endif
                                        </div>
                                    @elseif ($type === 'image')
                                        <div
                                            style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
                                            @foreach ($result->question->images as $image)
                                                <div
                                                    style="padding: 16px; border-radius: 12px; border: 2px solid {{ $result->image_id == $image->id ? ($image->correct ? '#22c55e' : '#ef4444') : ($image->correct ? '#22c55e' : '#e2e8f0') }}; background: {{ $image->correct ? 'rgba(34, 197, 94, 0.05)' : ($result->image_id == $image->id ? 'rgba(239, 68, 68, 0.05)' : 'rgba(255, 255, 255, 0.8)') }}; text-align: center; position: relative;">
                                                    @if ($image->img_name)
                                                        <img class="img-custom"
                                                            style="width: 100%; max-width: 150px; height: auto; border-radius: 8px; margin-bottom: 12px;"
                                                            src="{{ asset('images_options/' . $image->img_name) }}"
                                                            alt="Image option">
                                                    @endif

                                                    <div
                                                        style="display: flex; justify-content: center; gap: 8px; flex-wrap: wrap;">
                                                        @if ($image->correct == 1)
                                                            <span
                                                                style="background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); color: white; padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: 600;">คำตอบที่ถูก</span>
                                                        @endif
                                                        @if ($result->image_id == $image->id)
                                                            <span
                                                                style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); color: white; padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: 600;">คำตอบของคุณ</span>
                                                        @endif
                                                    </div>

                                                    @if ($image->correct == 1)
                                                        <div
                                                            style="position: absolute; top: 8px; right: 8px; color: #22c55e; font-size: 20px;">
                                                            ✓</div>
                                                    @elseif ($result->image_id == $image->id && !$image->correct)
                                                        <div
                                                            style="position: absolute; top: 8px; right: 8px; color: #ef4444; font-size: 20px;">
                                                            ✗</div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                        @if (is_null($result->image_id))
                                            <div
                                                style="padding: 16px; border-radius: 12px; border: 2px dashed #fbbf24; background: rgba(251, 191, 36, 0.05); text-align: center; margin-top: 16px;">
                                                <span
                                                    style="font-weight: 600; color: #f59e0b; font-style: italic;">ไม่ได้ตอบคำถามนี้</span>
                                            </div>
                                        @endif
                                    @elseif ($type === 'written')
                                        <div style="display: grid; gap: 12px;">
                                            <!-- Your Answer -->
                                            <div
                                                style="padding: 16px; border-radius: 12px; border: 2px solid {{ $result->correct ? '#22c55e' : '#ef4444' }}; background: {{ $result->correct ? 'rgba(34, 197, 94, 0.05)' : 'rgba(239, 68, 68, 0.05)' }};">
                                                <div style="display: flex; align-items: center; gap: 12px;">
                                                    <div
                                                        style="width: 32px; height: 32px; border-radius: 50%; background: {{ $result->correct ? 'linear-gradient(135deg, #22c55e 0%, #16a34a 100%)' : 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)' }}; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 14px;">
                                                        {{ $result->correct ? '✓' : '✗' }}
                                                    </div>
                                                    <div style="flex: 1;">
                                                        <p
                                                            style="font-size: 16px; font-weight: 500; color: #1e293b; margin: 0;">
                                                            {{ replaceMixedFractions($result->written_answer) }}</p>
                                                        <span
                                                            style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); color: white; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 600; margin-top: 4px; display: inline-block;">คำตอบของคุณ</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Correct Answer -->
                                            <div
                                                style="padding: 16px; border-radius: 12px; border: 2px solid #22c55e; background: rgba(34, 197, 94, 0.05);">
                                                <div style="display: flex; align-items: center; gap: 12px;">
                                                    <div
                                                        style="width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 14px;">
                                                        ✓
                                                    </div>
                                                    <div style="flex: 1;">
                                                        <p
                                                            style="font-size: 16px; font-weight: 500; color: #1e293b; margin: 0;">
                                                            {{ replaceMixedFractions($result->question->written_answer) }}
                                                        </p>
                                                        <span
                                                            style="background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); color: white; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 600; margin-top: 4px; display: inline-block;">คำตอบที่ถูก</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @elseif ($type === 'fraction')
                                        <div style="display: grid; gap: 12px;">
                                            @if ($result->question->fractions->first()->type === 'options')
                                                @foreach ($result->question->fractions as $fractions)
                                                    <div
                                                        style="padding: 16px; border-radius: 12px; border: 2px solid {{ $result->fraction == $fractions->id ? ($fractions->correct ? '#22c55e' : '#ef4444') : ($fractions->correct ? '#22c55e' : '#e2e8f0') }}; background: {{ $fractions->correct ? 'rgba(34, 197, 94, 0.05)' : ($result->fraction == $fractions->id ? 'rgba(239, 68, 68, 0.05)' : 'rgba(255, 255, 255, 0.8)') }}; position: relative;">
                                                        <div style="display: flex; align-items: center; gap: 12px;">
                                                            <div
                                                                style="width: 32px; height: 32px; border-radius: 50%; background: {{ $fractions->correct ? 'linear-gradient(135deg, #22c55e 0%, #16a34a 100%)' : ($result->fraction == $fractions->id ? 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)' : 'linear-gradient(135deg, #64748b 0%, #475569 100%)') }}; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 14px;">
                                                                {{ $fractions->correct ? '✓' : '✗' }}
                                                            </div>
                                                            <div style="flex: 1;">
                                                                <p
                                                                    style="font-size: 16px; font-weight: 500; color: #1e293b; margin: 0;">
                                                                    @if ($fractions->answer_type == 'mixed')
                                                                        {{ toMixedFraction( $fractions->numerator, $fractions->denominator,null) }}
                                                                    @else
                                                                        {{ toFraction($fractions->numerator, $fractions->denominator) }}
                                                                    @endif
                                                                </p>
                                                                <div style="margin-top: 4px; display: flex; gap: 8px;">
                                                                    @if ($fractions->correct == 1)
                                                                        <span
                                                                            style="background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); color: white; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 600;">คำตอบที่ถูก</span>
                                                                    @endif
                                                                    @if ($result->fraction == $fractions->id)
                                                                        <span
                                                                            style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); color: white; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 600;">คำตอบของคุณ</span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @else
                                                <div
                                                    style="padding: 16px; border-radius: 12px; border: 2px solid {{ $result->correct ? '#22c55e' : '#ef4444' }}; background: {{ $result->correct ? 'rgba(34, 197, 94, 0.05)' : 'rgba(239, 68, 68, 0.05)' }};">
                                                    <div style="display: flex; align-items: center; gap: 12px;">
                                                        <div
                                                            style="width: 32px; height: 32px; border-radius: 50%; background: {{ $result->correct ? 'linear-gradient(135deg, #22c55e 0%, #16a34a 100%)' : 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)' }}; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 14px;">
                                                            {{ $result->correct ? '✓' : '✗' }}
                                                        </div>
                                                        <div style="flex: 1;">
                                                            <p
                                                                style="font-size: 16px; font-weight: 500; color: #1e293b; margin: 0;">
                                                                {{ replaceMixedFractions($result->fraction) }}
                                                            </p>
                                                            <span
                                                                style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); color: white; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 600; margin-top: 4px; display: inline-block;">คำตอบของคุณ</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div
                                                    style="padding: 16px; border-radius: 12px; border: 2px solid #22c55e; background: rgba(34, 197, 94, 0.05);">
                                                    <div style="display: flex; align-items: center; gap: 12px;">
                                                        <div
                                                            style="width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 14px;">
                                                            ✓
                                                        </div>
                                                        <div style="flex: 1;">
                                                            <p
                                                                style="font-size: 16px; font-weight: 500; color: #1e293b; margin: 0;">
                                                                @if ($result->question->fractions->first()->answer_type == 'mixed')
                                                                    {{ toMixedFraction($result->question->fractions->first()->numerator, $result->question->fractions->first()->denominator,null) }}
                                                                @else
                                                                    {{ toFraction($result->question->fractions->first()->numerator, $result->question->fractions->first()->denominator) }}
                                                                @endif
                                                            </p>
                                                            <span
                                                                style="background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); color: white; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 600; margin-top: 4px; display: inline-block;">คำตอบที่ถูก</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <span class="font-bold italic">Question unanswered.</span>
                                    @endif

                                </div>

                                @if ($result->question->answer_explanation || $result->question->answer_explanation_image)
                                    <!-- Answer Explanation Section -->
                                    <div style="margin-top: 24px; padding: 20px; background: rgba(255, 255, 255, 0.8); border-radius: 12px; border: 1px solid rgba(249, 115, 22, 0.2); backdrop-filter: blur(5px);">
                                        <h4 style="font-size: 16px; font-weight: 600; color: #64748b; margin: 0 0 12px 0; text-transform: uppercase; letter-spacing: 0.5px;">
                                            💡 คำอธิบายคำตอบ
                                        </h4>
                                        @if ($result->question->answer_explanation)
                                            <p style="font-size: 16px; font-weight: 500; color: #1e293b; margin: 0 0 16px 0; line-height: 1.6;">
                                                {!! nl2br(e($result->question->answer_explanation)) !!}
                                            </p>
                                        @endif
                                        @if ($result->question->answer_explanation_image)
                                            <div style="text-align: center; margin-top: 16px;">
                                                <img class="answer-explain-thumb"
                                                     src="{{ asset('img_questions/' . $result->question->answer_explanation_image) }}"
                                                     alt="Answer Explanation Image"
                                                     style="max-width: 50%; height: auto; border-radius: 8px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); transition: transform 0.2s ease;">
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection
@push('scripts')
    <script type="text/javascript" async
        src="https://cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.7/MathJax.js?config=TeX-MML-AM_CHTML"></script>
    <script>
        // function insertFrac() {
        //     let question_text = document.querySelector('#text');
        //     if (question_text) {
        //         question_text.value += ' Frac(เศษ,ส่วน) ';
        //     }
        // }

        // function insertMixed() {
        //     let question_text = document.querySelector('#text');
        //     if (question_text) {
        //         question_text.value += ' MixFrac(เศษ,ส่วน) ';
        //     }
        // }
        // ป้องกันการกด backspace
        // Prevent back button navigation
        window.addEventListener('beforeunload', function(event) {
            event.preventDefault();
            event.returnValue = '';
        });

        window.addEventListener('popstate', function(event) {
            event.preventDefault();
            history.pushState(null, null, window.location.href);
            return false;
        });

        // Alternative method: Replace current history entry
        if (window.history && window.history.pushState) {
            window.history.replaceState(null, null, window.location.href);
            window.addEventListener('popstate', function() {
                window.history.replaceState(null, null, window.location.href);
            });
        }

        // Push a new state to make sure the popstate event is triggered
        history.pushState(null, null, window.location.href);
    </script>
@endpush
