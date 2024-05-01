<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\NotificationRequest;

class NotificationController extends Controller
{
    public function index() {

        $notifications = Notification::getAllForUser();

        return $this->ok("Notifications", $notifications);
    }

    public function store(NotificationRequest $request) {

        DB::beginTransaction();

        try {

            Notification::deleteAll();

            foreach ($request->days as $day) {
                Notification::createNotification($day, $request->time);
            }

            DB::commit();

            return $this->ok("Notification saved");
        
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return $this->error("Unable to save notification at the moment", 500);
        }




    }
}
