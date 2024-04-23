<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function loginWithGoogle()
    {

        return Socialite::driver('google')->redirect();
    }

    public function callbackFromGoogle()
    {
        try {
            $user = Socialite::driver('google')->user();
            $existingUser = User::where('email', $user->getEmail())->first();

            if ($existingUser) {
                // Check if the user is disabled
                if ($existingUser->disable == 1) {
                    // User is disabled, redirect back to login with error message
                    return redirect('/login')->withErrors([
                        'email' => 'Your account has been disabled. Please contact the administrator.',
                    ]);
                }
                // User is not disabled, log in the existing user
                Auth::login($existingUser);
                return redirect('/home');
            } else {
                // User does not exist, redirect to login with error message
                return redirect('/login')->withErrors([
                    'email' => 'Your email has not been registered. Please register.',
                ]);
            }
        } catch (\Throwable $th) {
            throw $th; // Handle any exceptions or errors as needed
        }
    }
}
