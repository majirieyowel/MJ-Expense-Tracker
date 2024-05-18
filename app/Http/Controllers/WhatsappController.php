<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Services\ExpenseService;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\WhatsAppSetupRequest;

class WhatsappController extends Controller
{

    const ALLOWED_MESSAGE_TYPE = 'text';

    const INVALID_COMMAND = 'Invalid Command ❌';

    const EXPENSE_SAVED = 'Saved ✅';

    const SUMMARY_COMMANDS = ['s', 'su', 'sum', 'summary'];

    protected $user;

    public function __construct(
        protected WhatsAppService $whatsappService,
        protected ExpenseService $expenseService
    ) {
    }

    public function checkWhatsappMessage()
    {
        $data['active'] = !is_null(Auth::user()->whatsapp_msisdn_verified_at);
        return $this->ok("Whatsapp message status", $data);
    }

    public function verifyMetaWebhook(Request $request)
    {
        $hubMode = $request->query('hub_mode');
        $hubChallenge = $request->query('hub_challenge');
        $hubToken = $request->query('hub_verify_token');

        if ($hubMode === "subscribe" && $hubToken === config('services.meta.verify_token')) {
            return response($hubChallenge, 200);
        } else {
            return response("", 403);
        }
    }


    public function setup(WhatsAppSetupRequest $request)
    {

        $user = $request->user();
        $user->whatsapp_msisdn = $request->phone_number;
        $user->save();

        $url = sprintf("https://wa.me/%s?text=sign+me+up", config('services.meta.whatsapp_number'));

        return $this->ok("Phone number saved", [
            'link_url' => $url,
        ]);
    }

    public function disconnect(Request $request)
    {

        $user = $request->user();
        $user->whatsapp_msisdn = null;
        $user->whatsapp_msisdn_verified_at = null;
        $user->save();

        // Send mail here

        return $this->ok("Whatsapp disconnected");
    }


    public function processWebhook(Request $request)
    {
        try {

            $data = $request['entry'][0]['changes'][0]['value'];

            if (isset($data['statuses'])) {
                // Handle delivery status

                return response('', 200);
            }

            $contactSection = $data['contacts'][0];
            $messageSection = $data['messages'][0];

            // check first time user. 
            $msisdn = $contactSection['wa_id'];

            $this->user = $user = User::getByWhatsappMsisdn($msisdn);

            if (!$user) {
                $message = <<<EOT
                *Unable to link account*

                Please setup your account on 
                https://spenda.ng
                EOT;

                $this->whatsappService->sendMessage($msisdn, $message);

                return response('', 200);
            }

            if (!$user->whatsapp_msisdn_verified_at) {

                $user->whatsapp_msisdn_verified_at = now();
                $user->save();

                $message = $this->welcomeMessage($contactSection['profile']['name']);

                $this->whatsappService->sendMessage($msisdn, $message);

                return response('', 200);
            }

            $message = $this->processMessageBody($messageSection);

            $this->whatsappService->sendMessage($msisdn, $message, $messageSection['id']);
        } catch (\Throwable $th) {
            report($th);
        }
    }

    private function processMessageBody(array $messageSection): string
    {
        $messageType = $messageSection['type'];
        $messageBody = trim($messageSection['text']['body']);

        if ($messageType !== self::ALLOWED_MESSAGE_TYPE) {
            return self::INVALID_COMMAND;
        }

        // check for summary
        if (in_array(strtolower($messageBody), self::SUMMARY_COMMANDS)) {


            return $this->getSummary();
        }

        return $this->processExpenseLogging($messageBody);
    }

    private function processExpenseLogging($message): string
    {
        $validator = Validator::make(['message' => $message], [
            'message' => ['required', 'regex:/^[a-zA-Z0-9._\-\s]+$/']
        ], [
            'message.regex' => 'Invalid character used ❌'
        ]);

        if ($validator->fails()) {
            return $validator->errors()->first();
        };

        if (preg_match('/(.*?)(\s*\d+(?:\.\d+)?)$/', $message, $matches)) {

            $item = $matches[1];
            $amount = $matches[2];

            return $this->validateAndSaveExpense($item, $amount);
        } else {
            return self::INVALID_COMMAND;
        }
    }

    private function validateAndSaveExpense(string $item, $amount): string
    {
        $validator = Validator::make(['amount' => $amount], [
            'amount' => ['required', 'numeric', 'gt:0']
        ]);

        if ($validator->fails()) {
            return self::INVALID_COMMAND;
        }
        $date = now()->format('Y-m-d');

        $this->expenseService->store($item, $amount, $date, $this->user->id);

        return self::EXPENSE_SAVED;
    }

    private function getSummary(): string
    {
        $summary = $this->expenseService->summary($this->user->id);

        $today = number_format($summary['today'], 2);
        $thisWeek = number_format($summary['past_week'], 2);
        $thisMonth = number_format($summary['this_month'], 2);
        $thisYear = number_format($summary['this_year'], 2);

        $message = <<<EOT
        *Account Summary*

        *Today:* {$this->user->currency_symbol}{$today}

        *This Week:* {$this->user->currency_symbol}{$thisWeek}

        *This Month:* {$this->user->currency_symbol}{$thisMonth}

        *This Year:* {$this->user->currency_symbol}{$thisYear}
        
        EOT;

        return $message;
    }

    private function welcomeMessage(string $name): string
    {

        $message =  <<<EOT
        Hello $name, 
        You are now connected via whatsapp 🎉.

        *INSTRUCTIONS*
        Save Expense:
        <EXPENSE NAME> <AMOUNT>
        _e.g Light bill 50.99_

        Account Summary:
        "summary"

        
        EOT;

        return $message;
    }
}
