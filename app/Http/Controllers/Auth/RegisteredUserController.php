<?php

namespace App\Http\Controllers\Auth;


use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Constants\AuthChannel;
use App\Http\Controllers\Controller;
use App\Http\Requests\SignUpRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(SignUpRequest $request): Response
    {
        $user = User::createUser((object)[
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'auth_channel' => AuthChannel::PASSWORD
        ]);

        event(new Registered($user));

        Auth::login($user);

        return response()->noContent();
    }
}
