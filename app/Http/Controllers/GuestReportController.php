<?php

namespace App\Http\Controllers;

use App\Models\GuestQuizzer;
use App\Support\Branch\BranchAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GuestReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        abort_unless($request->user()->can('student-list'), 403);

        if ($request->ajax()) {
            $query = GuestQuizzer::query();
            if (app('datatables.request')->orderableColumns() === []) {
                $query->orderByDesc('id');
            }
            // Guests have no owning branch, so branch staff read the shared list but only central staff change it.
            $is_central_actor = BranchAccess::isCentral($request->user());
            $canDelete = $is_central_actor && $request->user()->can('student-delete');
            $can_withdraw_marketing = $is_central_actor && $request->user()->can('student-edit');

            return datatables()->of($query)
                ->orderColumn('first_name', function ($query, $order) {
                    $query->orderBy('first_name', $order)->orderBy('last_name', $order)->orderBy('nickname', $order);
                })
                ->filterColumn('first_name', function ($query, $keyword) {
                    $query->where(fn ($name) => $name->where('first_name', 'like', "%{$keyword}%")
                        ->orWhere('last_name', 'like', "%{$keyword}%")
                        ->orWhere('nickname', 'like', "%{$keyword}%"));
                })
                ->editColumn('checkbox', function ($row) use ($canDelete) {
                    return $canDelete
                        ? '<input type="checkbox" id="' . $row->id . '" class="flat" name="table_records[]" value="' . $row->id . '" >'
                        : '';
                })
                ->addColumn('name', function ($row) {
                    return $row->first_name . ' ' . $row->last_name . ($row->nickname ? ' (' . $row->nickname . ')' : '');
                })
                ->addColumn('status', function ($row) {
                    if ($row->isExpired()) {
                        return '<span class="badge bg-danger" style="background-color: #dc3545; color: white;">' . e(__('students.guest_reports.expired')) . '</span>';
                    }
                    return '<span class="badge bg-success" style="background-color: #198754; color: white;">' . e(__('students.guest_reports.active')) . '</span>';
                })
                ->editColumn('created_at', function ($row) {
                    return $row->created_at ? $row->created_at->format('d-m-Y H:i') : '-';
                })
                ->editColumn('expires_at', function ($row) {
                    return $row->expires_at ? $row->expires_at->format('d-m-Y H:i') : '-';
                })
                ->addColumn('marketing_status', function ($row) {
                    return match ($row->marketing_consent) {
                        true => __('students.guest_reports.marketing_consented'),
                        false => __('students.guest_reports.marketing_not_consented'),
                        null => __('students.guest_reports.marketing_not_recorded'),
                    };
                })
                ->addColumn('action', function ($row) use ($canDelete, $can_withdraw_marketing) {
                    $withdraw_button = $can_withdraw_marketing && $row->marketing_consent === true
                        ? '<button type="button" data-rowid="' . $row->id . '" class="btn btn-sm btn-warning btn-withdraw-marketing">' . e(__('students.guest_reports.marketing_withdraw')) . '</button> '
                        : '';
                    $delete_button = $canDelete
                        ? '<button type="button" data-rowid="' . $row->id . '" class="btn btn-sm btn-danger btn-delete"><i class="fa fa-trash"></i> ' . e(__('common.actions.delete')) . '</button>'
                        : '';

                    return $withdraw_button . $delete_button;
                })
                ->rawColumns(['checkbox', 'status', 'action'])
                ->toJson();
        }

        return view('guest-quiz.report');
    }

    public function WithdrawMarketingConsent(Request $request)
    {
        $this->authorizeCentralAccess($request, 'student-edit');
        $validated_input = $request->validate(['id' => 'required|integer|exists:guest_quizzers,id']);

        $guest = GuestQuizzer::findOrFail($validated_input['id']);
        if ($guest->marketing_consent === true) {
            $guest->update(['marketing_consent' => false, 'marketing_consent_recorded_at' => now()]);
            Log::info('MARKETING_CONSENT_WITHDRAWN', ['actor_id' => $request->user()->id, 'guest_id' => $guest->id]);
        }

        return response()->json(['success' => true]);
    }

    public function destroy(Request $request)
    {
        $this->authorizeCentralAccess($request, 'student-delete');

        $guest = GuestQuizzer::find($request->input('id'));
        if (!$guest) {
            return response()->json(['success' => false], 404);
        }

        $guest->deleteTestData();
        $guest->delete();

        return response()->json(['success' => true]);
    }

    private function authorizeCentralAccess(Request $request, string $permission): void
    {
        $actor = $request->user();
        abort_unless($actor && BranchAccess::isCentral($actor) && $actor->can($permission), 403);
    }
}
