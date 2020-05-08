<?php

use App\Webhook;
use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

(Dotenv::createImmutable(__DIR__ . '/../'))->load();
(new Webhook())->run();
