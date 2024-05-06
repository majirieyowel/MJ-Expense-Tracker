<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\SignInRequest;
use App\Http\Requests\SignUpRequest;
use App\Mail\EmailVerificationMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function me(Request $request)
    {

        return $this->ok("User Created", Auth::user());
    }

    public function signUp(SignUpRequest $request)
    {

        $createdUser = User::signUpUser((object)[
            'username' => $request->username,
            'email' => $request->email,
            'password' => $request->password,
        ]);

        $userToken = User::signInUser($createdUser);

        // Send email
        Mail::to($createdUser)->send(new EmailVerificationMail());

        $data = [
            "user" => $createdUser,
            "token" => $userToken
        ];

        return $this->ok("User Created", $data);
    }

    public function signIn(SignInRequest $request)
    {

        $user = User::getByEmail($request->email);

        if (!$user) {
            return $this->error("Invalid credentials", 401);
        }

        if (!Hash::check($request->password, $user->password)) {
            return $this->error("Invalid credentials", 401);
        }

        $userToken = User::signInUser($user);

        $data = [
            "user" => $user,
            "token" => $userToken
        ];

        return $this->ok("Logged In", $data);
    }

    public function GoogleAuth(Request $request)
    {
    }
}
