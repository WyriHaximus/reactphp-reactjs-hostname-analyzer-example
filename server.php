<?php

define('WEBROOT', __DIR__ . DIRECTORY_SEPARATOR . 'webroot');

use Clue\React\Sse\BufferedChannel;
use GuzzleHttp\Client;
use League\Event\Emitter;
use React\EventLoop\Factory;
use React\Filesystem\Filesystem;
use React\Http\Server as HttpServer;
use React\Socket\Server as SocketServer;
use WyriHaximus\React\Examples\HostnameAnalyzer\Listeners\ChannelListener;
use WyriHaximus\React\Examples\HostnameAnalyzer\Listeners\DnsListener;
use WyriHaximus\React\Examples\HostnameAnalyzer\Listeners\GeoListener;
use WyriHaximus\React\Examples\HostnameAnalyzer\Listeners\TitleListener;
use WyriHaximus\React\Examples\HostnameAnalyzer\ResponseHandler;
use WyriHaximus\React\RingPHP\HttpClientAdapter;

require 'vendor/autoload.php';

$loop = Factory::create();
$socket = new SocketServer($loop);
$http = new HttpServer($socket, $loop);
$filesystem = Filesystem::create($loop);
$dns = (new \React\Dns\Resolver\Factory())->createCached('8.8.8.8', $loop);
$guzzle = new Client([
    'handler' => new HttpClientAdapter($loop, null, $dns),
]);
$channel = new BufferedChannel();
$emitter = new Emitter();

$emitter->useListenerProvider(new TitleListener($emitter, $guzzle));
$emitter->useListenerProvider(new DnsListener($emitter, $dns));
$emitter->useListenerProvider(new GeoListener($emitter, $guzzle));
$emitter->useListenerProvider(new ChannelListener($emitter, $channel));

$files = $filesystem->dir(WEBROOT)->ls();

$http->on('request', new ResponseHandler($files, $filesystem, $emitter, $channel));

$socket->listen(1337);
$loop->run();
