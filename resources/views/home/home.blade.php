@extends('layouts.quiz_layout')

@section('title', 'Home Page')

@push('CSS')
@endpush
@push('script')
@endpush
@section('content')
    <!-- Hero Section -->
    <div style="background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); padding: 48px 0; margin-bottom: 32px;">
        <div style="max-width: 1280px; margin: 0 auto; padding: 0 24px; text-align: center;">
            <h1 style="color: white; font-size: 48px; font-weight: 800; margin-bottom: 16px; text-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                🎯 eiMaths Quiz Platform
            </h1>
            <p style="color: rgba(255,255,255,0.9); font-size: 20px; font-weight: 300; max-width: 600px; margin: 0 auto;">
                เรียนรู้คณิตศาสตร์อย่างสนุกสนานผ่านแบบทดสอบที่หลากหลาย
            </p>
        </div>
    </div>

    <div style="padding: 0 0 48px 0;">
        <div style="max-width: 1280px; margin: 0 auto; padding: 0 24px;">
            <!-- Main Content Card -->
            <div style="background: white; border-radius: 16px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); border: 2px solid #f97316; overflow: hidden;">
                <div style="padding: 32px;">
                    <!-- Header Section -->
                    <div style="text-align: center; margin-bottom: 40px;">
                        <h2 style="color: #1f2937; font-size: 32px; font-weight: 700; margin-bottom: 12px; display: flex; align-items: center; justify-content: center; gap: 12px;">
                            <span style="font-size: 36px;">📚</span>
                            แบบทดสอบสำหรับนักเรียน
                        </h2>
                        <div style="width: 80px; height: 4px; background: linear-gradient(90deg, #f97316, #ea580c); margin: 0 auto; border-radius: 2px;"></div>
                    </div>

                    <!-- Quiz Grid -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px;">
                        @switch($levelCheck)
                            @case(1)
                                @forelse($public_quizzes_term as $term => $quiz_term)
                                    <div style="position: relative;">
                                        <a href="{{ route('home.term', [$level, $term]) }}" style="text-decoration: none; display: block;">
                                            <div style="background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%); border: 2px solid #fed7aa; border-radius: 16px; padding: 24px; transition: all 0.3s ease; position: relative; overflow: hidden; height: 160px; display: flex; flex-direction: column; justify-content: space-between;" onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 20px 25px -5px rgba(249, 115, 22, 0.2), 0 10px 10px -5px rgba(249, 115, 22, 0.1)'; this.style.borderColor='#f97316';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)'; this.style.borderColor='#fed7aa';">
                                                <!-- Background Pattern -->
                                                <div style="position: absolute; top: -20px; right: -20px; width: 80px; height: 80px; background: rgba(249, 115, 22, 0.1); border-radius: 50%; z-index: 1;"></div>
                                                <div style="position: absolute; bottom: -30px; left: -30px; width: 100px; height: 100px; background: rgba(249, 115, 22, 0.05); border-radius: 50%; z-index: 1;"></div>

                                                <div style="position: relative; z-index: 2;">
                                                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
                                                        <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #f97316, #ea580c); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; color: white; font-weight: 600;">{{ $term }}</div>
                                                        <div>
                                                            <h3 style="color: #1f2937; font-size: 20px; font-weight: 700; margin: 0; line-height: 1.2;">Term {{ $term }}</h3>
                                                            <p style="color: #6b7280; font-size: 14px; margin: 4px 0 0 0;">เทอม {{ $term }}</p>
                                                        </div>
                                                    </div>

                                                    <div style="display: flex; align-items: center; gap: 8px;">
                                                        <div style="width: 32px; height: 32px; background: rgba(249, 115, 22, 0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                                            <span style="font-size: 16px;">📊</span>
                                                        </div>
                                                        <div>
                                                            <p style="color: #374151; font-size: 16px; font-weight: 600; margin: 0;">{{ $quiz_term->groupBy('section')->count() }} ระดับ</p>
                                                            <p style="color: #9ca3af; font-size: 12px; margin: 0;">Levels Available</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                @empty
                                    <div style="grid-column: 1 / -1; text-align: center; padding: 48px; color: #6b7280;">
                                        <div style="font-size: 48px; margin-bottom: 16px;">📝</div>
                                        <h3 style="font-size: 20px; font-weight: 600; margin-bottom: 8px;">ไม่พบแบบทดสอบ</h3>
                                        <p style="font-size: 16px;">ขณะนี้ยังไม่มีแบบทดสอบสาธารณะ</p>
                                    </div>
                                @endforelse
                            @break

                            @case(2)
                                @forelse($public_quizzes_term as $section => $quiz_section)
                                    <div style="position: relative;">
                                        <a href="{{ route('home.section', [$level, $term, $section]) }}" style="text-decoration: none; display: block;">
                                            <div style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border: 2px solid #bae6fd; border-radius: 16px; padding: 24px; transition: all 0.3s ease; position: relative; overflow: hidden; height: 160px; display: flex; flex-direction: column; justify-content: space-between;" onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 20px 25px -5px rgba(14, 165, 233, 0.2), 0 10px 10px -5px rgba(14, 165, 233, 0.1)'; this.style.borderColor='#0ea5e9';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)'; this.style.borderColor='#bae6fd';">
                                                <!-- Background Pattern -->
                                                <div style="position: absolute; top: -20px; right: -20px; width: 80px; height: 80px; background: rgba(14, 165, 233, 0.1); border-radius: 50%; z-index: 1;"></div>
                                                <div style="position: absolute; bottom: -30px; left: -30px; width: 100px; height: 100px; background: rgba(14, 165, 233, 0.05); border-radius: 50%; z-index: 1;"></div>

                                                <div style="position: relative; z-index: 2;">
                                                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
                                                        <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #0ea5e9, #0284c7); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 18px; color: white; font-weight: 600;">L{{ $section }}</div>
                                                        <div>
                                                            <h3 style="color: #1f2937; font-size: 20px; font-weight: 700; margin: 0; line-height: 1.2;">Level {{ $section }}</h3>
                                                            <p style="color: #6b7280; font-size: 14px; margin: 4px 0 0 0;">ระดับ {{ $section }}</p>
                                                        </div>
                                                    </div>

                                                    <div style="display: flex; align-items: center; gap: 8px;">
                                                        <div style="width: 32px; height: 32px; background: rgba(14, 165, 233, 0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                                            <span style="font-size: 16px;">🎯</span>
                                                        </div>
                                                        <div>
                                                            <p style="color: #374151; font-size: 16px; font-weight: 600; margin: 0;">{{ $quiz_section->count() }} แบบทดสอบ</p>
                                                            <p style="color: #9ca3af; font-size: 12px; margin: 0;">Quizzes Available</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                @empty
                                    <div style="grid-column: 1 / -1; text-align: center; padding: 48px; color: #6b7280;">
                                        <div style="font-size: 48px; margin-bottom: 16px;">📝</div>
                                        <h3 style="font-size: 20px; font-weight: 600; margin-bottom: 8px;">ไม่พบแบบทดสอบ</h3>
                                        <p style="font-size: 16px;">ขณะนี้ยังไม่มีแบบทดสอบสาธารณะ</p>
                                    </div>
                                @endforelse
                            @break

                            @case(3)
                                @forelse($public_quizzes_term as $quiz)
                                    <div style="position: relative;">
                                        <a href="{{ route('quiz.show', $quiz->slug) }}" style="text-decoration: none; display: block;">
                                            <div style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border: 2px solid #bbf7d0; border-radius: 16px; padding: 24px; transition: all 0.3s ease; position: relative; overflow: hidden; height: 160px; display: flex; flex-direction: column; justify-content: space-between;" onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 20px 25px -5px rgba(34, 197, 94, 0.2), 0 10px 10px -5px rgba(34, 197, 94, 0.1)'; this.style.borderColor='#22c55e';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)'; this.style.borderColor='#bbf7d0';">
                                                <!-- Background Pattern -->
                                                <div style="position: absolute; top: -20px; right: -20px; width: 80px; height: 80px; background: rgba(34, 197, 94, 0.1); border-radius: 50%; z-index: 1;"></div>
                                                <div style="position: absolute; bottom: -30px; left: -30px; width: 100px; height: 100px; background: rgba(34, 197, 94, 0.05); border-radius: 50%; z-index: 1;"></div>

                                                <div style="position: relative; z-index: 2;">
                                                    <div style="margin-bottom: 16px;">
                                                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                                                            <div style="width: 32px; height: 32px; background: linear-gradient(135deg, #22c55e, #16a34a); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                                                <span style="font-size: 16px; color: white;">📋</span>
                                                            </div>
                                                            <span style="background: rgba(34, 197, 94, 0.1); color: #16a34a; padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 600;">QUIZ</span>
                                                        </div>
                                                        <h3 style="color: #1f2937; font-size: 18px; font-weight: 700; margin: 0; line-height: 1.3; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">{{ $quiz->title }}</h3>
                                                    </div>

                                                    <div style="display: flex; align-items: center; gap: 8px;">
                                                        <div style="width: 32px; height: 32px; background: rgba(34, 197, 94, 0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                                            <span style="font-size: 16px;">❓</span>
                                                        </div>
                                                        <div>
                                                            <p style="color: #374151; font-size: 16px; font-weight: 600; margin: 0;">{{ $quiz->questions_count }} คำถาม</p>
                                                            <p style="color: #9ca3af; font-size: 12px; margin: 0;">Questions</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                @empty
                                    <div style="grid-column: 1 / -1; text-align: center; padding: 48px; color: #6b7280;">
                                        <div style="font-size: 48px; margin-bottom: 16px;">📝</div>
                                        <h3 style="font-size: 20px; font-weight: 600; margin-bottom: 8px;">ไม่พบแบบทดสอบ</h3>
                                        <p style="font-size: 16px;">ขณะนี้ยังไม่มีแบบทดสอบสาธารณะ</p>
                                    </div>
                                @endforelse
                            @break

                            @default
                                @forelse($public_quizzes_level as $level => $quiz_level)
                                    <div style="position: relative;">
                                        <a href="{{ route('home.level', $level) }}" style="text-decoration: none; display: block;">
                                            <div style="background: linear-gradient(135deg, #fefce8 0%, #fef3c7 100%); border: 2px solid #fde68a; border-radius: 16px; padding: 24px; transition: all 0.3s ease; position: relative; overflow: hidden; height: 160px; display: flex; flex-direction: column; justify-content: space-between;" onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 20px 25px -5px rgba(245, 158, 11, 0.2), 0 10px 10px -5px rgba(245, 158, 11, 0.1)'; this.style.borderColor='#f59e0b';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)'; this.style.borderColor='#fde68a';">
                                                <!-- Background Pattern -->
                                                <div style="position: absolute; top: -20px; right: -20px; width: 80px; height: 80px; background: rgba(245, 158, 11, 0.1); border-radius: 50%; z-index: 1;"></div>
                                                <div style="position: absolute; bottom: -30px; left: -30px; width: 100px; height: 100px; background: rgba(245, 158, 11, 0.05); border-radius: 50%; z-index: 1;"></div>

                                                <div style="position: relative; z-index: 2;">
                                                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
                                                        <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #f59e0b, #d97706); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 16px; color: white; font-weight: 700;">{{ substr($level, 0, 2) }}</div>
                                                        <div>
                                                            <h3 style="color: #1f2937; font-size: 20px; font-weight: 700; margin: 0; line-height: 1.2;">{{ $level }}</h3>
                                                            <p style="color: #6b7280; font-size: 14px; margin: 4px 0 0 0;">ระดับการศึกษา</p>
                                                        </div>
                                                    </div>

                                                    <div style="display: flex; align-items: center; gap: 8px;">
                                                        <div style="width: 32px; height: 32px; background: rgba(245, 158, 11, 0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                                            <span style="font-size: 16px;">📚</span>
                                                        </div>
                                                        <div>
                                                            <p style="color: #374151; font-size: 16px; font-weight: 600; margin: 0;">{{ $quiz_level->groupBy('term')->count() }} เทอม</p>
                                                            <p style="color: #9ca3af; font-size: 12px; margin: 0;">Terms Available</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                @empty
                                    <div style="grid-column: 1 / -1; text-align: center; padding: 48px; color: #6b7280;">
                                        <div style="font-size: 48px; margin-bottom: 16px;">📝</div>
                                        <h3 style="font-size: 20px; font-weight: 600; margin-bottom: 8px;">ไม่พบแบบทดสอบ</h3>
                                        <p style="font-size: 16px;">ขณะนี้ยังไม่มีแบบทดสอบสาธารณะ</p>
                                    </div>
                                @endforelse
                        @endswitch
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-orange-500">
                    <div class="p-6 text-gray-900">
                        <h6 class="text-xl font-bold mb-5">Quizzes for Registered Users</h6>
                        <div class="flex flex-wrap">
                            @switch($levelCheck)
                                @case(1)
                                    @forelse($registered_only_quizzes as $term => $quiz_term)
                                        <div class="px-4 py-2 w-full lg:w-6/12 xl:w-3/12">
                                            <a href="{{ route('home.term', [$level, $term]) }}">
                                                <div
                                                    class="flex relative flex-col mb-6 min-w-0 break-words bg-white rounded shadow-lg xl:mb-0 border-2 border-orange-500">
                                                    <div class="flex-auto p-4">
                                                        Term {{ $term }}
                                                        <p class="text-sm">quizzes count: <span>{{ $quiz_term->count() }}</span>
                                                        </p>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    @empty
                                        <div class="mt-2">No public quizzes found.</div>
                                    @endforelse
                                @break

                                @case(2)
                                    @forelse($registered_only_quizzes as $section => $quiz_section)
                                        <div class="px-4 py-2 w-full lg:w-6/12 xl:w-3/12">
                                            <a href="{{ route('home.section', [$level, $term, $section]) }}">
                                                <div
                                                    class="flex relative flex-col mb-6 min-w-0 break-words bg-white rounded shadow-lg xl:mb-0 border-2 border-orange-500">
                                                    <div class="flex-auto p-4">
                                                        Level-{{ $section }}
                                                        <p class="text-sm">Questions: <span>{{ $quiz_section->count() }}</span></p>

                                                        </p>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    @empty
                                        <div class="mt-2">No public quizzes found.</div>
                                    @endforelse
                                @break

                                @case(3)
                                    @forelse($registered_only_quizzes as  $quiz)
                                        <div class="px-4 py-2 w-full lg:w-6/12 xl:w-3/12">
                                            <a href="{{ route('quiz.show', $quiz->slug) }}">
                                                <div
                                                    class="flex relative flex-col mb-6 min-w-0 break-words bg-white rounded shadow-lg xl:mb-0 border-2 border-orange-500">
                                                    <div class="flex-auto p-4">
                                                        {{ $quiz->title }}
                                                        <p class="text-sm">Questions2: <span>{{ $quiz->questions_count }}</span></p>

                                                        </p>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    @empty
                                        <div class="mt-2">No public quizzes found.</div>
                                    @endforelse
                                @break

                                @default
                                    @forelse($registered_only_quizzes as $level => $quiz_level)
                                        <div class="px-4 py-2 w-full lg:w-6/12 xl:w-3/12">
                                            <a href="{{ route('home.level', $level) }}">
                                                <div
                                                    class="flex relative flex-col mb-6 min-w-0 break-words bg-white rounded shadow-lg xl:mb-0 border-2 border-orange-500">
                                                    <div class="flex-auto p-4">
                                                        <p><span class="text-lg font-bold">{{ $level }}</span></p>
                                                        <p class="text-base">Quizzes: <span>{{ $quiz_level->count() }}</span>
                                                        </p>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    @empty
                                        <div class="mt-2">No public quizzes found.</div>
                                    @endforelse
                                @endswitch

                            </div>
                        </div>
                    </div>
                </div>
            </div> --}}



@endsection

@push('script')
@endpush
