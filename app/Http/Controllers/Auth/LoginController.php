<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;


class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function login(Request $request)
    {
        // Attempt to login
        $login = $this->attemptLogin($request);

        if ($login) {
            $user = Auth::user();

            // Check if user is disabled
            if ($user->disable == 1) {
                auth()->logout();
                return redirect()->route('login')
                    ->with('login_error', 'Please contact the administrator.')
                    ->withErrors(['email' => 'Please contact the administrator.']);
            }

            // Check if user has the 'affiliate-user' role
            if ($user->hasRole('Affiliate-user')) {
                return redirect()->route('welcome');
            }
        
            if ($user->hasRole('User')) {

                return redirect()->route('welcome');

            }

            // Proceed with the default login response
            return $this->sendLoginResponse($request);
        }

        // Authentication failed
        return $this->sendFailedLoginResponse($request);
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        $user = Socialite::driver('google')->user();

        // You can now create or authenticate the user using $user information.
        // For example:
        // $authUser = User::firstOrCreate(['email' => $user->getEmail()]);
        // Auth::login($authUser);

        return redirect('/home'); // Redirect to the desired page after login.
    }
}
