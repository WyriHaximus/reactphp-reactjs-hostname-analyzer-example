<?php

namespace WyriHaximus\React\Examples\HostnameAnalyzer\Listeners;

use GuzzleHttp\Client;
use GuzzleHttp\Message\Response;
use League\Event\Emitter;
use League\Event\ListenerAcceptorInterface;
use League\Event\ListenerProviderInterface;

class GeoListener implements ListenerProviderInterface
{
    /**
     * @var Emitter
     */
    protected $emitter;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @param Client $client
     */
    public function __construct(Emitter $emitter, CLient $client)
    {
        $this->emitter = $emitter;
        $this->client = $client;
    }

    public function provideListeners(ListenerAcceptorInterface $acceptor)
    {
        $acceptor->addListener('ip', function ($event, $ip) {
            $this->client->get('https://freegeoip.net/json/' . $ip, [
                'future' => true,
            ])->then(function (Response $response) {
                $this->emitter->emit('sse', [
                    'type' => 'geo',
                    'payload' => $response->json()['region_name'],
                ]);
            });
        });
    }
}
