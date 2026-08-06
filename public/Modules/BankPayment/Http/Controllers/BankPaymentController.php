<?php

namespace Modules\BankPayment\Http\Controllers;

use App\DepositRecord;
use App\Services\Images\ImageConversionOptions;
use App\Services\Images\ImageConversionResult;
use App\Services\Images\ImageConversionService;
use App\Services\Images\ImageSourceContext;
use App\Traits\ImageStore;
use App\Traits\SendNotification;
use App\User;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Modules\BankPayment\Entities\BankPaymentRequest;
use Modules\Organization\Entities\OrganizationFinance;
use Modules\Organization\Events\CourseSellCommissionEvent;
use Throwable;

class BankPaymentController extends Controller
{
    use ImageStore, SendNotification;

    private ImageConversionService $image_conversion_service;

    public function __construct(ImageConversionService $image_conversion_service)
    {
        $this->image_conversion_service = $image_conversion_service;
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $payments = BankPaymentRequest::latest()->paginate(10);
        return view('bankpayment::index', compact('payments'));

    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('bankpayment::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return bool
     */
    public function store(Request $request)
    {

        $stored_absolute_path = null;
        $image_path = null;

        try {

            if ($request->hasFile('image')) {
                [$stored_absolute_path, $image_path] = $this->StoreBankPaymentImage($request->file('image'));
            }

            DB::transaction(function () use ($request, $image_path): void {
                $payment = new BankPaymentRequest();
                $payment->user_id = Auth::user()->id ?? 0;
                $payment->bank_name = $request->bank_name;
                $payment->branch_name = $request->branch_name;
                $payment->account_number = $request->account_number;
                $payment->account_holder = $request->account_holder;
                $payment->account_type = $request->type;
                $payment->amount = $request->deposit_amount;
                $payment->image = $image_path ?? null;
                $payment->save();
            });

            Toastr::success(trans('payment.Your request has pending. Please wait for approved'), trans('common.Success'));

            return true;
        } catch (Throwable $e) {
            if ($stored_absolute_path !== null && is_file($stored_absolute_path)) {
                @unlink($stored_absolute_path);
            }
            Toastr::error($e->getMessage(), trans('common.Failed'));
            return false;

        }
    }

    /** @return array{0: string, 1: string} */
    private function StoreBankPaymentImage(\Illuminate\Http\UploadedFile $uploaded_file): array
    {
        $folder_path = 'uploads/bankpayment';
        $destination_directory = public_path($folder_path);
        File::ensureDirectoryExists($destination_directory);
        $conversion_result = null;
        $stored_absolute_path = null;

        try {
            if (in_array(strtolower($uploaded_file->getClientOriginalExtension()), ['jpg', 'jpeg', 'png'], true)) {
                $conversion_result = $this->image_conversion_service->PrepareUploadedImage(
                    $uploaded_file,
                    ImageConversionOptions::ForOriginalDimensions(ImageSourceContext::BANK_PAYMENT_IMAGE),
                );
                $stored_extension = $conversion_result->extension;
                $source_path = $conversion_result->selected_path;
            } else {
                $stored_extension = strtolower($uploaded_file->getClientOriginalExtension());
                $source_path = $uploaded_file->getRealPath() ?: $uploaded_file->getPathname();
            }

            $stored_basename = Str::uuid()->toString() . '.' . $stored_extension;
            $stored_absolute_path = $destination_directory . DIRECTORY_SEPARATOR . $stored_basename;
            if (!File::copy($source_path, $stored_absolute_path)) {
                throw new \RuntimeException('The bank payment image could not be stored.');
            }

            return [$stored_absolute_path, 'public/' . $folder_path . '/' . $stored_basename];
        } catch (Throwable $exception) {
            if ($stored_absolute_path !== null && is_file($stored_absolute_path)) {
                @unlink($stored_absolute_path);
            }

            throw $exception;
        } finally {
            if ($conversion_result instanceof ImageConversionResult) {
                $this->image_conversion_service->CleanupPreparedImage($conversion_result);
            }
        }
    }


    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        if (demoCheck()) {
            return redirect()->back();
        }

        try {
            $request = BankPaymentRequest::findOrFail($id);
            $request->status = 1;
            $request->save();


            $result = $this->depositWithGateWay($request->amount, $request->user_id);
            if ($result) {
                return redirect()->back();
            } else {
                return redirect()->back();
            }


        } catch (\Exception $e) {
            GettingError($e->getMessage(), url()->current(), request()->ip(), request()->userAgent());
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {

        if (demoCheck()) {
            return redirect()->back();
        }
        try {
            $request = BankPaymentRequest::findOrFail($id);
            $request->delete();

            Toastr::success(trans('common.Operation successful'), trans('common.Success'));
            return redirect()->back();

        } catch (\Exception $e) {
            GettingError($e->getMessage(), url()->current(), request()->ip(), request()->userAgent());
        }
    }


    public function depositWithGateWay($amount, $user_id)
    {
        if (demoCheck()) {
            return redirect()->back();
        }
        try {


            if (Auth::check()) {
                DB::beginTransaction();
                $user = User::find($user_id);
                $user->balance += $amount;
                $user->save();
                $depositRecord = new DepositRecord();
                $depositRecord->user_id = $user->id;
                $depositRecord->method = "Bank Payment";
                $depositRecord->amount = $amount;
                $depositRecord->save();

                if (isModuleActive('Cashback') && $depositRecord) {
                    generateCashback($depositRecord->user_id, $depositRecord->amount, 'recharge', $depositRecord);
                }
                if (isModuleActive('Organization') && $user->isOrganization()) {
                    $data = [
                        'user_id' => $user->id,
                        'amount' => $amount,
                        'status' => true,
                        'type' => OrganizationFinance::$credit,
                        'description' => OrganizationFinance::$deposit_description,
                        'data_type' => OrganizationFinance::$type_deposit,
                        'payment_type' => OrganizationFinance::$payment_completed,
                    ];
                    event(new CourseSellCommissionEvent($data));
                }

                 $this->sendNotification('Bank_Payment', $user, [
                    'amount' => $amount,
                    'currency' => Settings('currency_code'),
                    'time' => now()->format(Settings('active_date_format') . ' H:i:s A')
                ]);


                Toastr::success(trans('common.Operation successful'), trans('common.Success'));

                DB::commit();
                return true;

            } else {
                DB::rollBack();
                Toastr::error(trans('frontend.Something Went Wrong'), trans('common.Error'));;
                return false;
            }


        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error(trans('frontend.Something Went Wrong'), trans('common.Error'));;
            return false;

        }
    }

}
