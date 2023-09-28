<?php

namespace App\Http\Controllers;

use App\Models\User;
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
            $saveUser = User::where('email', $user->getEmail())->first();

            if ($saveUser != "" && $saveUser != NULL) {
                //(Auth::guard('parent')->loginUsingId($saveUser->id));
                return redirect('/home');
            } else {
                return redirect('/login')->withErrors([
                    'email' => 'Your email has not been registered , Please register',
                ]);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
