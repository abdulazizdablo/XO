<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\User;

class VerifyEmailController extends Controller
{
    // method to find user and verify if his email has verified or not
    //if the email has verified return message 'your email is already verify'
    //else verified it and return message 'your email is verify'

    public function __invoke(Request $request)
    {
        $user = User::find($request->route('id'));

        if ($user->hasVerifiedEmail()) {
            return response()->json(["message"=>'your email is already verify'], 200);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return redirect('https://XO.ae/verify/success')->with("message",'your email is verify');

    }
}
