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
        $login = $this->attemptLogin($request);

        if ($login) {
            $user = Auth::user();
            if ($user->disable == 1) {
                auth()->logout();
                return redirect()->route('login')
                    ->with('login_error', 'Please contact the administrator.')
                    ->withErrors(['email' => 'Please contact the administrator.']);
            }
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
        $googleUser = Socialite::driver('google')->user();

        dd($googleUser);
        // Check if the user exists in your database
        $user = User::where('email', $googleUser->getEmail())->first();

        if (!$user) {
            // Handle user not found
            return redirect()->route('login')
                ->with('login_error', 'User not found.');
        }

        // Check if the user is disabled
        if ($user->disable == 1) {
            auth()->logout();
            return redirect()->route('login')
                ->with('login_error', 'Please contact the administrator.')
                ->withErrors(['email' => 'Please contact the administrator.']);
        }

        // Authenticate the user
        Auth::login($user);

        return redirect('/home'); // Redirect to the desired page after login.
    }
}
