<?php

namespace WyriHaximus\React\Examples\HostnameAnalyzer\Listeners;

use GuzzleHttp\Client;
use GuzzleHttp\Message\Response;
use League\Event\Emitter;
use League\Event\ListenerAcceptorInterface;
use League\Event\ListenerProviderInterface;

class TitleListener implements ListenerProviderInterface
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
        $acceptor->addListener('lookup', function ($event, $hostname) {
            $this->client->get('http://' . $hostname . '/', [
                'future' => true,
            ])->then(function (Response $response) {
                if (preg_match('/<title>(.+)<\/title>/', $response->getBody()->getContents(), $matches) && isset($matches[1])) {
                    $title = $matches[1];
                    $this->emitter->emit('sse', [
                        'type' => 'title',
                        'payload' => $title,
                    ]);
                }
            });
        });
    }
}
