<?php

namespace App\Http\Controllers;

use App\Services\Faq\FaqReadRange;
use App\Services\Faq\FaqReadReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Validation\ValidationException;
use Throwable;

final class FaqStatisticsController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:faq-list');
    }

    public function Index(Request $request, FaqReadReport $report)
    {
        try {
            $range = FaqReadRange::From($request->query());
        } catch (ValidationException $exception) {
            if ($request->expectsJson()) {
                throw $exception;
            }
            $filter_values = [];
            foreach (['range', 'start_date', 'end_date'] as $filter_name) {
                $submitted_filter = $request->query($filter_name);
                $filter_values[$filter_name] = is_string($submitted_filter) ? $submitted_filter : '';
            }
            return response()->view('faqs.statistics', [
                'range' => FaqReadRange::From([]), 'rows' => null, 'report_error' => false,
                'validation_failed' => true, 'filter_values' => $filter_values,
                'errors' => (new ViewErrorBag())->put('default', $exception->validator->errors()),
            ], 422);
        }

        $view_data = ['range' => $range, 'rows' => null, 'report_error' => false,
            'validation_failed' => false, 'filter_values' => []];
        try {
            $rows = $report->Build($range, app()->getLocale());
            $rows->appends(['range' => $range->range, 'start_date' => $range->start_date, 'end_date' => $range->end_date]);
        } catch (Throwable $exception) {
            Log::warning('faq.statistics.failed', ['operation' => 'faq.statistics.read', 'outcome' => 'failed',
                'error_code' => 'FAQ_STATISTICS_UNAVAILABLE', 'correlation_id' => (string) Str::uuid()]);
            $view_data['report_error'] = true;
            return response()->view('faqs.statistics', $view_data, 503);
        }
        $view_data['rows'] = $rows;
        return view('faqs.statistics', $view_data);
    }
}
