<?php

namespace WyriHaximus\React\Examples\HostnameAnalyzer\Listeners;

use League\Event\Emitter;
use League\Event\ListenerAcceptorInterface;
use League\Event\ListenerProviderInterface;
use React\Dns\Resolver\Resolver;

class DnsListener implements ListenerProviderInterface
{
    /**
     * @var Emitter
     */
    protected $emitter;

    /**
     * @var Resolver
     */
    protected $resolver;

    public function __construct(Emitter $emitter, Resolver $resolver)
    {
        $this->emitter = $emitter;
        $this->resolver = $resolver;
    }

    public function provideListeners(ListenerAcceptorInterface $acceptor)
    {
        $acceptor->addListener('lookup', function ($event, $hostname) {
            $this->resolver->resolve($hostname)->then(function ($ip) {
                $this->emitter->emit('ip', $ip);
                $this->emitter->emit('sse', [
                    'type' => 'dns',
                    'payload' => $ip,
                ]);
            });
        });
    }
}
