<?php

namespace Modules\Affiliate\Http\Controllers;

use App\Repositories\UserRepositoryInterface;
use App\StudentCustomField;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Modules\Affiliate\Repositories\AffiliateRepository;
use Modules\FrontendManage\Entities\LoginPage;
use Modules\RolePermission\Entities\Permission;
use Modules\RolePermission\Entities\Role;
use Modules\RolePermission\Entities\RolePermission;


class AffiliateAuthController extends Controller
{
    use  RegistersUsers;


    protected $affiliateRepo, $userRepository;


    public function __construct(AffiliateRepository $affiliateRepo, UserRepositoryInterface $userRepository)
    {
        $this->middleware(['maintenanceMode', 'onlyAppMode']);
        $this->affiliateRepo = $affiliateRepo;
        $this->userRepository = $userRepository;
    }


    public function showRegistrationFrom()
    {

        try {

            $page = LoginPage::getData();
            $custom_field = StudentCustomField::getData();
            return view('affiliate::auth.registration', compact('page', 'custom_field'));
        } catch (Exception $e) {
            Toastr::error($e->getMessage(), trans('common.Error'));
            return response()->json(['error' => $e->getMessage()], 503);
        }
    }

    protected function redirectTo()
    {
        return '/affiliate/my_affiliate';

    }

    protected function validator(array $data)
    {

        if (saasEnv('nocaptcha_for_reg')) {
            $rules = [
                'name' => ['required', 'string', 'max:255'],
                'phone' => 'nullable|string|regex:/^([0-9\s\-\+\(\)]*)$/|min:1|unique:users',
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
                'g-recaptcha-response' => 'required|captcha'
            ];
        } else {
            $rules = [
                'name' => ['required', 'string', 'max:255'],
                'phone' => 'nullable|string|regex:/^([0-9\s\-\+\(\)]*)$/|min:1|unique:users',
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ];
        }

        if (isset($data['is_lms_signup'])) {
            $rules = [
                'name' => ['required', 'string', 'max:255'],
                'phone' => 'nullable|string|regex:/^([0-9\s\-\+\(\)]*)$/|min:1|unique:users',
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
                'institute_name' => ['required', 'string', 'max:255'],
                'domain' => ['required', 'string', 'max:20', 'unique:lms_institutes'],
            ];
        }

        return Validator::make($data, $rules,
            validationMessage($rules)
        );
    }

    protected function roleId()
    {
        $role = Role::where('name', 'Affiliate')->where('type', 'System')->first();
        if (!$role) {
            $role = Role::create([
                'name' => 'Affiliate',
                'type' => 'System',
            ]);
        }
        $this->updatePermission($role->id);
        return $role->id;
    }

    public function create($data)
    {
        if (empty($data['phone'])) {
            $data['phone'] = null;
        }
        $data['affiliate_request'] = 1;
        $data['role_id'] = $this->roleId();
        $data['password'] = Hash::make($data['password']);
        $data['accept_affiliate_request'] = affiliateConfig('admin_approval_need') ? 0 : 1;
        return $this->userRepository->create($data);

    }

    public function register(Request $request)
    {

        $this->validator($request->all())->validate();

        $user = $this->create($request->all());

        if (isModuleActive('LmsSaas') && !empty($user->institute) && $user->institute->domain != SaasDomain()) {
            if ($user->lms_id != 1) {
                $token = md5(uniqid());
                Storage::put($token, $request->email . '|' . $request->password);
                $url = 'http://' . $user->institute->domain . '.' . config('app.short_url') . '/login?token=' . $token;
                return redirect()->to($url);
            }
        }
        event(new Registered($user));

        $this->guard()->login($user);

        if ($response = $this->registered($request, $user)) {
            return $response;
        }

        return $request->wantsJson()
            ? new JsonResponse([], 201)
            : redirect($this->redirectPath());

    }

    public function updatePermission($role_id)
    {
        $permission = Permission::where('route', 'affiliate')->first();
        $permission2 = Permission::where('route', 'affiliate.my_affiliate.index')->first();
        if ($permission && $permission2) {
            $role_permission = RolePermission::query()
                ->where('permission_id', $permission2->id)
                ->where('role_id', $role_id)
                ->first();
            if (!$role_permission) {
                $role_permission = new RolePermission();
                $role_permission->permission_id = $permission->id;
                $role_permission->role_id = $role_id;
                $role_permission->save();

                $role_permission = new RolePermission();
                $role_permission->permission_id = $permission2->id;
                $role_permission->role_id = $role_id;
                $role_permission->save();

                Cache::forget('PermissionList_' . SaasDomain());
                Cache::forget('RoleList_' . SaasDomain());
                Cache::forget('PolicyPermissionList_' . SaasDomain());
                Cache::forget('PolicyRoleList_' . SaasDomain());

            }
        }


    }

}
