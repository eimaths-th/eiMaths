<?php

namespace App\Http\Controllers;

use App\Models\AffiliateCommissionList;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\AffiliateUserBank;
use App\Models\User;
use App\Models\Department;
use App\Models\Position;
use Spatie\Permission\Models\Role;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Modules\Affiliate\Entities\AffiliateUserWallet;
use Modules\Affiliate\Entities\AffiliateWithdraw;

class CommissionListControler extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index2(Request $request)
    {
        //

        $list = AffiliateCommissionList::all();
    }

    public function index(Request $request)
    {
        /*  $data = User::orderBy('id', 'DESC')->paginate(5);
        $roles = Role::pluck('name', 'name')->all();
        return view('users.index', compact('data'))
            ->with('i', ($request->input('page', 1) - 1) * 5)
            ->with('roles', $roles); */
        if (!Gate::allows('all-centre')) {
            $roles = Role::pluck('name', 'id')->except([1, 4])->all();
        } else {
            $roles = Role::pluck('name', 'name')->all();
        }

        if ($request->ajax()) {
            //sleep(2);

            $datas =  AffiliateCommissionList::query()->orderBy("id", "desc")->get();
       
            return datatables()->of($datas)

                ->editColumn('ref', function ($row) {
                    return $row->order->ref ?? '';
                })
                ->editColumn('order_id', function ($row) {
                    return $row->order->order_number ?? '';
                })
                ->editColumn('reciept_id', function ($row) {
                    return $row->receipt->receipt_number ?? '';
                })
                ->editColumn('status', function ($row) {
                    return $row->status ?? '';
                })
                ->editColumn('commission_amount', function ($row) {
                    return $row->commission_amount ?? '';
                })
                ->editColumn('payment_to', function ($row) {
                    return $row->paymentTo->name ?? 'Null User';
                })
                ->editColumn('approved_by', function ($row) {
                    return $row->approvedBy->name ?? '';
                })
                ->editColumn('created_at', function ($row) {
                    return $row->created_at->format('d-m-Y') ?? ''
                    ;
                })
                ->editColumn('checkbox', function ($row) {
                    return '<input type="checkbox" id="' . $row->id . '" class="flat" name="table_records[]" value="' . $row->id . '" >';
                })
                ->addColumn('action', function ($row) {

                    $html = '';
                    if ($row->status === 'paid') {
                        // Button for "Approve"
                        $html .= '<button class="btn btn-success btn-sm approve-btn" data-id="' . $row->id . '">Approve</button>';

                        // Button for "Reject"
                        $html .= ' <button class="btn btn-danger btn-sm reject-btn" data-id="' . $row->id . '">Reject</button>';
                    }

                    return $html;
                })->rawColumns(['checkbox', 'action'])->toJson();
        }
        return view('commission_ist.index');
    }
    /**
     * Show the form for creating a new resource.
     */

    public function updateStatus(Request $request)
    {
        $id = $request->id;
        $action = $request->action;

        // Validate the action
        if (!in_array($action, ['approve', 'reject'])) {
            return response()->json(['message' => 'Invalid action'], 400);
        }

        // Find the commission record
        $commission = AffiliateCommissionList::find($id);
        if (!$commission) {
            return response()->json(['message' => 'Commission record not found'], 404);
        }

        // $withdrawRequest = AffiliateWithdraw::find($id);
        // if (!$withdrawRequest) {
        //     return response()->json(['error' => 'Withdraw request not found'], 404);
        // }

        // Find the user's wallet
        $UserWallet = AffiliateUserWallet::where('user_id', $commission->payment_to)->first();
        if (!$UserWallet) {
            return response()->json(['error' => 'User wallet not found'], 404);
        }

        //find the user's bank
        $UserBank = AffiliateUserBank::where('user_id', $commission->payment_to)->first();
        if (!$UserBank) {
            return response()->json(['error' => 'User bank not found'], 404);
        }

        $appoved_by = Auth::user()->id;

        $amount = $commission->commission_amount;
        $user_id = $commission->payment_to;

        // Start a transaction to ensure consistency
        DB::beginTransaction();
        try {
            // Update the wallet amount if the action is "approve"
            if ($action === 'approve') {
                AffiliateUserWallet::where('user_id', $user_id)
                    ->increment('amount', $amount);
            }

            // Update the commission status
            $commission->status = $action === 'approve' ? 'approved' : 'rejected';
            $commission->approved_by = $appoved_by;
            $commission->save();

            // Commit the transaction
            DB::commit();

            return response()->json(['message' => 'Status updated successfully!']);
        } catch (\Exception $e) {
            // Rollback the transaction on error
            DB::rollBack();
            return response()->json([
                'message' => 'An error occurred while updating the status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
