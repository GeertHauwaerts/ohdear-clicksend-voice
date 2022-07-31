<?php

namespace App;

use App\OhDear;
use App\ClickSend;
use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class OhDearTest extends TestCase
{
    const DOCKER_URI = 'http://ocv-web:8000';

    protected $client;
    protected $ohdear;

    private $payload = [
        'type' => 'test',
        'uuid' => false,
    ];

    public static function setUpBeforeClass(): void
    {
        self::clearCache();
    }

    protected function setUp(): void
    {
        $this->client = new Client([
            'base_uri' => self::DOCKER_URI,
            'exceptions' => false,
        ]);

        $this->ohdear = new OhDear();
    }

    private static function clearCache(): void
    {
        $clicksend = new ClickSend();
        $clicksend->clearCachedRecipients();
    }

    /**
     * @dataProvider forTestAlert
     */
    public function testTestAlert($cached): void
    {
        $this->payload['uuid'] = Uuid::uuid4();

        $response = $this->client->post('/', [
            'json' => $this->payload,
            'headers' => [
                'OhDear-Signature' => $this->ohdear->signature($this->payload),
            ],
        ]);

        $this->assertEquals(200, $response->getStatusCode());

        $json = json_decode($response->getBody(), false);
        $this->assertEquals($this->payload['uuid'], $json->uuid);

        foreach ($json->recipients as $r => $c) {
            $this->assertEquals($cached, $c);
        }
    }

    public function forTestAlert(): array
    {
        return [
            [false],
            [true],
        ];
    }

    public static function tearDownAfterClass(): void
    {
        self::clearCache();
    }
}
