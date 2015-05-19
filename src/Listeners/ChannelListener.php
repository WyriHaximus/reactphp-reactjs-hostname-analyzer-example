<?php

namespace WyriHaximus\React\Examples\HostnameAnalyzer\Listeners;

use Clue\React\Sse\BufferedChannel;
use League\Event\Emitter;
use League\Event\ListenerAcceptorInterface;
use League\Event\ListenerProviderInterface;

class ChannelListener implements ListenerProviderInterface
{
    /**
     * @var Emitter
     */
    protected $emitter;

    /**
     * @var BufferedChannel
     */
    protected $channel;

    public function __construct(Emitter $emitter, BufferedChannel $channel)
    {
        $this->emitter = $emitter;
        $this->channel = $channel;
    }

    public function provideListeners(ListenerAcceptorInterface $acceptor)
    {
        $acceptor->addListener('sse', function ($event, $data) {
            $this->channel->writeMessage(json_encode($data));
        });
    }
}
