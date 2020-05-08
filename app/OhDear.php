<?php

namespace App;

class OhDear
{
    private $required = [
        'CONTENT_TYPE',
        'HTTP_OHDEAR_SIGNATURE',
    ];

    private $filter = [
        'HTTP/1.1',
    ];

    private $body;
    private $payload;

    public function __construct()
    {
        $this->setPayload();
    }

    public function isSigned()
    {
        foreach ($this->required as $r) {
            if (!isset($_SERVER[$r])) {
                return false;
            }
        }

        if ($_SERVER['CONTENT_TYPE'] !== 'application/json') {
            return false;
        }

        $sig = hash_hmac('sha256', $this->body, $_ENV['OHDEAR_WEBHOOK_SECRET']);

        if ($sig !== $_SERVER['HTTP_OHDEAR_SIGNATURE']) {
            return false;
        }

        return true;
    }

    public function getType()
    {
        if (isset($this->payload->type)) {
            return $this->payload->type;
        }

        return '';
    }

    public function getSiteLabel()
    {
        if (isset($this->payload->site->label)) {
            return $this->payload->site->label;
        }

        return '';
    }

    public function getCheckerResultError()
    {
        $error = '';

        if (isset($this->payload->run->result_payload->checkerResult1->error->description)) {
            $error = $this->payload->run->result_payload->checkerResult1->error->description;
        }

        foreach ($this->filter as $f) {
            $error = str_replace($f, '', $error);
        }

        return $error;
    }

    private function setPayload()
    {
        $this->body = file_get_contents('php://input');
        $this->payload = json_decode($this->body);
    }
}
