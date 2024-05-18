<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WhatsAppService
{

    /**
     * Send a message to a whatsapp user
     * 
     * @param string $msisdn The phone number
     * @param string $message The message to send
     * @param string $message_id Message to reply
     */
    public function sendMessage(string $msisdn, string $message, string $message_id = null)
    {

        $config = config('services.meta');

        $url = sprintf("https://graph.facebook.com/v19.0/%s/messages", $config['phone_number_id']);

        $token = $config['api_token'];

        $data  = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $msisdn,
            'type' => 'text',
            'text' => [
                'body' => $message,
            ],
        ];

        if ($message_id) {
            $data = array_merge($data, [
                'context' => [
                    'message_id' => $message_id
                ]
            ]);
        }

        return Http::withToken($token)->post($url, $data)->body();
    }
}
