<?php

namespace App;

use Predis\Client;

class Cache
{
    private $client;

    const DEFAULT_TTL = 1800;

    public function __construct()
    {
        $this->client = new Client([
            'host' => $_ENV['REDIS_HOSTNAME'],
        ]);
    }

    public function getClient()
    {
        return $this->client;
    }

    public function has($key): bool
    {
        return $this->client->exists(hash('md5', $key));
    }

    public function get($key, $default = null)
    {
        $res = $this->client->get(hash('md5', $key));

        if ($res === null) {
            return $default;
        }

        return unserialize($res);
    }

    public function set($key, $value, $ttl = self::DEFAULT_TLL): bool
    {
        $this->client->set(hash('md5', $key), serialize($value));

        if ($ttl) {
            $this->client->expire(hash('md5', $key), $ttl);
        }

        return true;
    }

    public function delete($key): bool
    {
        $this->client->del(hash('md5', $key));
        return true;
    }
}
