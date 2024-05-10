<?php

namespace App\Listeners;

use App\Models\User;
use Illuminate\Support\Str;
use App\Constants\AuthChannel;
use App\Services\MailgunService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Auth\Events\Registered;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendEmailVerificationMail
{


    /**
     * Handle the event.
     */
    public function handle(Registered $event): void
    {
        if ($event->user->auth_channel === AuthChannel::PASSWORD && App::environment(['production'])) {

            $cacheKey = Str::random(30);
            $cacheValue = $event->user->id;

            cache([$cacheKey => $cacheValue], now()->addWeek());

            (new MailgunService())->send_with_template("email_verification", [
                "subject" => "Email Verification",
                "to" => $event->user->username . " " . $event->user->email,
                "variables" => [
                    "verification_url" => "https://spenda.ng/verify/{$cacheKey}",
                    "email" => $event->user->email,
                    "contact_url" => "https://spenda.ng/contact-us"
                ]
            ]);
        }
    }
}
