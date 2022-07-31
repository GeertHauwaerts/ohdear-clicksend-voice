<?php

namespace App;

use App\Cache;
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
    private $cache;
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
        $this->cache = new Cache();

        $this->setRecipients();
    }

    public function sendVoiceMessage($msg, $voice = 'female', $lang = 'en-us')
    {
        $collection = [];

        if (empty($msg)) {
            return;
        }

        foreach ($this->recipients as $r) {
            $did = $this->pnu->format($r, PhoneNumberFormat::E164);

            if ($this->isCachedRecipient($did)) {
                continue;
            }

            $this->setCachedRecipient($did);

            $vm = new VoiceMessage();
            $vm->setTo($did);
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

    public function getRecipients()
    {
        $res = [];

        foreach ($this->recipients as $r) {
            $res[] = $this->pnu->format($r, PhoneNumberFormat::E164);
        }

        return $res;
    }

    public function isCachedRecipient($recipient)
    {
        $key = "OCV::recipient::{$recipient}";

        if ($this->cache->has($key)) {
            return true;
        }

        return false;
    }

    public function setCachedRecipient($recipient, $snooze = 0)
    {
        if ($snooze === 0) {
            $snooze = $_ENV['CLICKSEND_CALL_RECIPIENTS'];
        }

        $key = "OCV::recipient::{$recipient}";
        $this->cache->set($key, 1, $snooze);
    }

    public function clearCachedRecipients()
    {
        foreach ($this->getRecipients() as $r) {
            $this->cache->delete("OCV::recipient::{$r}");
        }
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
