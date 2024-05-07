<?php

namespace App\Services;

use Mailgun\Mailgun;

class MailgunService
{

    /**
     * @var string $api_key | Mailgun API key
     */
    private $api_key;

    /**
     * @var string $domain | Caller domain on mailgun
     */
    private $domain;

    private $from = "Spenda <noreply@spenda.ng>";


    public function __construct()
    {

        $api_key =  \config('services.mailgun.key');

        $domain =  \config('services.mailgun.domain');

        if (!$api_key || !$domain) {
            throw new \Exception('Missing Mailgun credentials');
        };

        $this->api_key =  $api_key;

        $this->domain =  $domain;
    }

    /**
     * Send email through mailgun template client.
     * 
     * @param String $template
     * @param array $payload 
     */
    public function send_with_template($template, array $payload)
    {
        $mgClient = Mailgun::create($this->api_key);

        $params = array(
            'from'                  => $this->from,
            'to'                    => $payload['to'],
            'subject'               => $payload['subject'],
            'template'              => $template,
            'h:X-Mailgun-Variables' => json_encode($payload['variables'])
        );

        # Make the call to the client.
        return $mgClient->messages()->send($this->domain, $params);
    }
}
