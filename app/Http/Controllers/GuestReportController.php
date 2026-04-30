<?php

namespace App\Http\Controllers;

use App\Models\GuestQuizzer;
use Illuminate\Http\Request;

class GuestReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = GuestQuizzer::query()->orderBy('id', 'desc');

            return datatables()->of($query)
                ->editColumn('checkbox', function ($row) {
                    return '<input type="checkbox" id="' . $row->id . '" class="flat" name="table_records[]" value="' . $row->id . '" >';
                })
                ->addColumn('name', function ($row) {
                    return $row->first_name . ' ' . $row->last_name . ($row->nickname ? ' (' . $row->nickname . ')' : '');
                })
                ->addColumn('status', function ($row) {
                    if ($row->isExpired()) {
                        return '<span class="badge bg-danger" style="background-color: #dc3545; color: white;">Expired</span>';
                    }
                    return '<span class="badge bg-success" style="background-color: #198754; color: white;">Active</span>';
                })
                ->editColumn('created_at', function ($row) {
                    return $row->created_at ? $row->created_at->format('d-m-Y H:i') : '-';
                })
                ->editColumn('expires_at', function ($row) {
                    return $row->expires_at ? $row->expires_at->format('d-m-Y H:i') : '-';
                })
                ->addColumn('action', function ($row) {
                    $html = '<button type="button" data-rowid="' . $row->id . '" class="btn btn-sm btn-danger btn-delete"><i class="fa fa-trash"></i> Delete</button>';
                    return $html;
                })
                ->rawColumns(['checkbox', 'status', 'action'])
                ->toJson();
        }

        return view('guest-quiz.report');
    }

    public function destroy(Request $request)
    {
        $id = $request->input('id');
        $guest = GuestQuizzer::find($id);
        if ($guest) {
            $guest->deleteTestData();
            $guest->delete();
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false], 404);
    }
}
