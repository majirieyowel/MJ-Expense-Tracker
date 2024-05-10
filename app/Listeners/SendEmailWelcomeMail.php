<?php

namespace App\Listeners;

use App\Models\User;
use App\Models\EmailQueue;
use Illuminate\Support\Facades\App;
use Illuminate\Auth\Events\Registered;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendEmailWelcomeMail
{

    /**
     * Handle the event.
     */
    public function handle(Registered $event): void
    {
        if (App::environment(['production'])) {

            EmailQueue::create([
                "template" => "welcome",
                "email" => $event->user->email,
                "send_after_ts" => now()->addMinutes(1)->timestamp,
                "data" => json_encode([
                    "subject" => "Welcome to Spenda",
                    "to" => $event->user->username . " " . $event->user->email,
                    "variables" => [
                        "username" => $event->user->username,
                        "contact_url" => "https://spenda.ng/contact-us"
                    ]
                ])
            ]);
        }
    }
}
