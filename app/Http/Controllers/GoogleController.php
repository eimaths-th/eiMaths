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
                Auth::login($existingUser); // Log in the existing user
                return redirect('/home');
            } else {
                return redirect('/login')->withErrors([
                    'email' => 'Your email has not been registered. Please register.',
                ]);
            }
        } catch (\Throwable $th) {
            throw $th; // Handle any exceptions or errors as needed
        }
    }
}
