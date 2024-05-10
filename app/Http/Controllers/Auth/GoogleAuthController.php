<?php

namespace App\Http\Controllers\Auth;

use Google\Client;
use App\Models\User;
use Illuminate\Support\Str;
use App\Constants\AuthChannel;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Registered;
use App\Http\Requests\GoogleAuthRequest;

class GoogleAuthController extends Controller
{
    public function __invoke(GoogleAuthRequest $request)
    {
        try {

            $client = new Client(['client_id' => $request->client_id]);
            $payload = $client->verifyIdToken($request->credential);

            if ($payload) {

                $email = $payload['email'];

                $user = User::getByEmail($email);

                if ($user) {

                    Auth::login($user);

                    return response()->noContent();
                } else {

                    $createdUser = User::createUser((object)[
                        'username' => $payload['name'],
                        'email' => $email,
                        'password' => Str::random(),
                        'auth_channel' => AuthChannel::GOOGLE
                    ], true);

                    event(new Registered($createdUser));

                    Auth::login($createdUser);

                    return response()->noContent();

                }
            } else {
                return $this->error("Unable to sign in with google at this time", 400);
            }
        } catch (\Throwable $th) {
            report($th);
            return $this->error("Unable to sign in with google at this time", 400);
        }
    }
}
