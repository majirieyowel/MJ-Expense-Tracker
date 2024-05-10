<?php

namespace App\Console\Commands;

use App\Models\EmailQueue;
use Illuminate\Console\Command;
use App\Services\MailgunService;
use Illuminate\Support\Facades\Log;

class RunQueuedEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:run-queued-emails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $queuedEmails = EmailQueue::where('send_after_ts', '<', now()->timestamp)->take(10)->get();

        Log::debug(sprintf("%s emails retrieved from queue at %s", $queuedEmails->count(), now()->format('jS M Y \a\t g:i a')));

        foreach ($queuedEmails as $data) {
            
            (new MailgunService())->send_with_template($data->template, json_decode($data->data, true));

            // $email->delete();
        }
    }
}
