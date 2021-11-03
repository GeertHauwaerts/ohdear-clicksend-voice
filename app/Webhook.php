<?php

namespace App;

use App\ClickSend;
use App\OhDear;

class Webhook
{
    private $required = [
        'CLICKSEND_API_USERNAME',
        'CLICKSEND_API_PASSWORD',
        'OHDEAR_WEBHOOK_SECRET',
    ];

    private $ohdear;
    private $clicksend;

    public function __construct()
    {
        $this->checkConfig();
        $this->ohdear = new OhDear();
        $this->clicksend = new ClickSend();
    }

    public function run()
    {
        if (!$this->ohdear->isSigned()) {
            $this->unauthorized();
        }

        $type = ucfirst($this->ohdear->getType());

        if (!empty($type) && method_exists($this, "type{$type}")) {
            $function = "type{$type}";
            $this->$function();
        }
    }

    private function checkConfig()
    {
        foreach ($this->required as $r) {
            if (!isset($_ENV[$r])) {
                $this->error();
            }
        }
    }

    private function typeUptimeCheckFailedNotification()
    {
        $msg = implode(' ', [
            "<speak><prosody volume='x-loud'>",
            "Attention! Monitoring detected that {$this->ohdear->getSiteLabel()} is down.",
            "The latest response was {$this->ohdear->getCheckerResultError()}.",
            '</prosody></speak>',
        ]);

        $this->clicksend->sendVoiceMessage($msg);
    }

    private function error()
    {
        header('HTTP/1.1 500 Internal Server Error');
        exit();
    }

    private function unauthorized()
    {
        header('HTTP/1.1 401 Unauthorized');
        exit();
    }
}
