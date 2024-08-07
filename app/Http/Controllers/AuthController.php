<?php

namespace App\Http\Controllers;

use Google\Client;
use App\Models\User;
use App\Models\EmailQueue;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Constants\AuthChannel;
use App\Http\Requests\SignInRequest;
use App\Http\Requests\SignUpRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use App\Http\Requests\GoogleAuthRequest;
use App\Events\ResendEmailVerificationMail;

class AuthController extends Controller
{
    public function me(Request $request)
    {
        $user = Auth::user();

        $user->notification = User::withNotification($user);

        unset($user->notifications);

        return $this->ok("Current user", $user);
    }

    public function signUp(SignUpRequest $request)
    {
        $createdUser = User::createUser((object)[
            'username' => $request->username,
            'email' => $request->email,
            'password' => $request->password,
            'auth_channel' => AuthChannel::PASSWORD
        ]);

        event(new Registered($createdUser));

        return $this->signInUser($createdUser);
    }

    public function signIn(SignInRequest $request)
    {
        $user = User::getByEmail($request->email);

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->error("Invalid credentials", 401);
        }

        return $this->signInUser($user);
    }

    public function GoogleAuth(GoogleAuthRequest $request)
    {
        try {

            $client = new Client(['client_id' => $request->client_id]);
            $payload = $client->verifyIdToken($request->credential);

            if ($payload) {

                $email = $payload['email'];

                $user = User::getByEmail($email);

                if ($user) {
                    return $this->signInUser($user);
                } else {

                    $createdUser = User::createUser((object)[
                        'username' => $payload['name'],
                        'email' => $email,
                        'password' => Str::random(),
                    ], true);

                    // Queue welcome email 
                    EmailQueue::create([
                        "template" => "welcome",
                        "email" => $createdUser->email,
                        "send_after_ts" => now()->addMinutes(2)->timestamp,
                        "data" => json_encode([
                            "subject" => "Welcome to Spenda",
                            "to" => $createdUser->username . " " . $createdUser->email,
                            "variables" => [
                                "username" => $createdUser->username,
                                "contact_url" => "https://spenda.ng/contact-us"
                            ]
                        ])
                    ]);



                    return $this->signInUser($createdUser);
                }
            } else {
                return $this->error("Unable to sign in with google at this time", 400);
            }
        } catch (\Throwable $th) {
            report($th);
            return $this->error("Unable to sign in with google at this time", 400);
        }
    }

    public function resendVerificationMail(Request $request)
    {

        $user = User::getByEmail($request->email);

        if (!$user) {
            return $this->error("Email not found.", 401);
        }

        event(new ResendEmailVerificationMail($user));

        return $this->ok("Email sent!", []);
    }

    private function signInUser(User $user)
    {
        $user->notification = User::withNotification($user);

        unset($user->notifications);

        $userToken = User::signInUser($user);

        $data = [
            "user" => $user,
            "token" => $userToken
        ];

        return $this->ok("User Created", $data);
    }
}
