@extends('layouts.qiuz_layout')

@section('title', 'Leader Board')

@push('CSS')
@endpush
@push('script')
@endpush
@section('content')
    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-4 min-w-full overflow-hidden overflow-x-auto align-middle sm:rounded-md">
                        <table class="min-w-full border divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="w-16 bg-gray-50 px-6 py-3 text-left">
                                    </th>
                                    <th class="bg-gray-50 px-6 py-3 text-left">
                                        <span
                                            class="text-xs font-medium uppercase leading-4 tracking-wider text-gray-500">Quiz
                                            Title
                                        </span>
                                    </th>
                                    <th class="bg-gray-50 px-6 py-3 text-left">
                                        <span
                                            class="text-xs font-medium uppercase leading-4 tracking-wider text-gray-500">Quiz
                                            Description
                                        </span>
                                    </th>
                                    <th class="bg-gray-50 px-6 py-3 text-left">
                                        <span
                                            class="text-xs font-medium uppercase leading-4 tracking-wider text-gray-500">Result</span>
                                    </th>
                                    <th class="bg-gray-50 px-6 py-3 text-left">
                                        <span
                                            class="text-xs font-medium uppercase leading-4 tracking-wider text-gray-500">Time
                                            spent</span>
                                    </th>
                                    <th class="bg-gray-50 px-6 py-3 text-left">
                                        <span
                                            class="text-xs font-medium uppercase leading-4 tracking-wider text-gray-500">Date
                                            Taken</span>
                                    </th>
                                    <th class="w-40 bg-gray-50 px-6 py-3 text-left">
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="bg-white divide-y divide-gray-200 divide-solid">
                                @forelse($tests as $test)
                                    <tr class="bg-white">
                                        <td class="px-6 py-4 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            {{ $loop->iteration }}
                                        </td>
                                        <td class="px-6 py-4 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            {{ $test->quiz->title }}
                                        </td>
                                        <td class="px-6 py-4 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            {{ $test->quiz->description }}
                                        </td>
                                        <td class="px-6 py-4 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            {{ $test->result . '/' . $test->quiz->questions_count }}
                                        </td>
                                        <td class="px-6 py-4 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            {{-- {{ sprintf('%.2f', $test->time_spent / 60) }} minutes --}}

                                            @php
                                                $hour = intdiv($test->time_spent, 3600);
                                                $minutes = intdiv($test->time_spent, 60);
                                                $seconds = $test->time_spent % 60;
                                            @endphp
                                            Time:
                                            {{ sprintf('%02d', $hour) }}:{{ sprintf('%02d', $minutes) }}:{{ sprintf('%02d', $seconds) }}
                                        </td>
                                        <td class="px-6 py-4 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            {{ $test->created_at->format('d/m/Y h:i A') }}
                                        </td>
                                        <td>
                                            <a href="{{ route('results.show', $test) }}"
                                                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8"
                                            class="px-6 py-4 text-center leading-5 text-gray-900 whitespace-no-wrap">
                                            No tests were found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        <!-- Custom Pagination -->
                        @if ($tests->hasPages())
                            <div style="display: flex; justify-content: center; align-items: center; margin-top: 24px; gap: 8px;">
                                {{-- Previous Page Link --}}
                                @if ($tests->onFirstPage())
                                    <span style="display: inline-flex; align-items: center; padding: 8px 12px; margin-left: 0; line-height: 1.25; color: #9ca3af; background-color: #f9fafb; border: 1px solid #d1d5db; border-radius: 6px; cursor: not-allowed;">
                                        <svg style="width: 16px; height: 16px; margin-right: 4px;" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                        ก่อนหน้า
                                    </span>
                                @else
                                    <a href="{{ $tests->previousPageUrl() }}" style="display: inline-flex; align-items: center; padding: 8px 12px; margin-left: 0; line-height: 1.25; color: #374151; background-color: #ffffff; border: 1px solid #d1d5db; border-radius: 6px; text-decoration: none; transition: all 0.15s ease-in-out;" onmouseover="this.style.backgroundColor='#f3f4f6'; this.style.color='#f97316';" onmouseout="this.style.backgroundColor='#ffffff'; this.style.color='#374151';">
                                        <svg style="width: 16px; height: 16px; margin-right: 4px;" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                        ก่อนหน้า
                                    </a>
                                @endif

                                {{-- Pagination Elements --}}
                                @foreach ($tests->getUrlRange(1, $tests->lastPage()) as $page => $url)
                                    @if ($page == $tests->currentPage())
                                        <span style="display: inline-flex; align-items: center; padding: 8px 12px; line-height: 1.25; color: #ffffff; background-color: #f97316; border: 1px solid #f97316; border-radius: 6px; font-weight: 600; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);">
                                            {{ $page }}
                                        </span>
                                    @else
                                        <a href="{{ $url }}" style="display: inline-flex; align-items: center; padding: 8px 12px; line-height: 1.25; color: #374151; background-color: #ffffff; border: 1px solid #d1d5db; border-radius: 6px; text-decoration: none; transition: all 0.15s ease-in-out;" onmouseover="this.style.backgroundColor='#f97316'; this.style.color='#ffffff'; this.style.borderColor='#f97316';" onmouseout="this.style.backgroundColor='#ffffff'; this.style.color='#374151'; this.style.borderColor='#d1d5db';">
                                            {{ $page }}
                                        </a>
                                    @endif
                                @endforeach

                                {{-- Next Page Link --}}
                                @if ($tests->hasMorePages())
                                    <a href="{{ $tests->nextPageUrl() }}" style="display: inline-flex; align-items: center; padding: 8px 12px; margin-left: 0; line-height: 1.25; color: #374151; background-color: #ffffff; border: 1px solid #d1d5db; border-radius: 6px; text-decoration: none; transition: all 0.15s ease-in-out;" onmouseover="this.style.backgroundColor='#f3f4f6'; this.style.color='#f97316';" onmouseout="this.style.backgroundColor='#ffffff'; this.style.color='#374151';">
                                        ถัดไป
                                        <svg style="width: 16px; height: 16px; margin-left: 4px;" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                    </a>
                                @else
                                    <span style="display: inline-flex; align-items: center; padding: 8px 12px; margin-left: 0; line-height: 1.25; color: #9ca3af; background-color: #f9fafb; border: 1px solid #d1d5db; border-radius: 6px; cursor: not-allowed;">
                                        ถัดไป
                                        <svg style="width: 16px; height: 16px; margin-left: 4px;" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                    </span>
                                @endif
                            </div>

                            <!-- Pagination Info -->
                            <div style="text-align: center; margin-top: 16px; color: #6b7280; font-size: 14px;">
                                แสดงผล {{ $tests->firstItem() }} ถึง {{ $tests->lastItem() }} จากทั้งหมด {{ $tests->total() }} รายการ
                            </div>
                        @endif

                    </div>
                </div>

            </div>
        </div>
    </div>

@endsection
@section('script')
@endsection
