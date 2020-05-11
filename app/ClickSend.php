<?php

namespace App;

use ClickSend\Api\VoiceApi;
use ClickSend\Configuration;
use ClickSend\Model\VoiceMessage;
use ClickSend\Model\VoiceMessageCollection;
use GuzzleHttp\Client;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

class ClickSend
{
    private $api;
    private $pnu;
    private $recipients;

    public function __construct()
    {
        $this->api = new VoiceApi(
            new Client(),
            Configuration::getDefaultConfiguration()
                ->setUsername($_ENV['CLICKSEND_API_USERNAME'])
                ->setPassword($_ENV['CLICKSEND_API_PASSWORD'])
        );

        $this->pnu = PhoneNumberUtil::getInstance();
        $this->setRecipients();
    }

    public function sendVoiceMessage($msg, $voice = 'female', $lang = 'en-us')
    {
        $collection = [];

        if (empty($msg)) {
            return;
        }

        foreach ($this->recipients as $r) {
            $vm = new VoiceMessage();
            $vm->setTo($this->pnu->format($r, PhoneNumberFormat::E164));
            $vm->setBody($msg);
            $vm->setVoice($voice);
            $vm->setCustomString('ohdear-clicksend-voice');
            $vm->setCountry($this->pnu->getRegionCodeForNumber($r));
            $vm->setSource('php');
            $vm->setRequireInput(0);
            $vm->setMachineDetection(1);
            $vm->setLang($lang);
            $collection[] = $vm;
        }

        $vmc = new VoiceMessageCollection();
        $vmc->setMessages($collection);

        $this->api->voiceSendPost($vmc);
    }

    private function setRecipients()
    {
        $recipients = explode(' ', $_ENV['CLICKSEND_CALL_RECIPIENTS']);

        foreach ($recipients as $r) {
            try {
                $r = $this->pnu->parse($r, null);
            } catch (NumberParseException $e) {
                continue;
            }

            if (!$this->pnu->isValidNumber($r)) {
                continue;
            }

            $this->recipients[] = $r;
        }
    }
}
