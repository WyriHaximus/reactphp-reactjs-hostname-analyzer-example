<?php

namespace WyriHaximus\React\Examples\HostnameAnalyzer;

use Clue\React\Sse\BufferedChannel;
use League\Event\Emitter;
use React\Filesystem\Filesystem;
use React\Filesystem\Node\File;
use React\Http\Request;
use React\Http\Response;
use React\Promise\RejectedPromise;

class ResponseHandler
{
    const LOOKED_PATH = '/lookup.json';
    const SSE_PATH = '/sse';

    protected $files;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var Emitter
     */
    protected $emitter;

    /**
     * @var BufferedChannel
     */
    protected $channel;

    protected $filesContents = [];

    public function __construct($files, Filesystem $filesystem, Emitter $emitter, BufferedChannel $channel)
    {
        $this->files = $files;
        $this->filesystem = $filesystem;
        $this->emitter = $emitter;
        $this->channel = $channel;
    }

    public function __invoke(Request $request, Response $response) {
        echo $request->getPath(), PHP_EOL;
        if ($request->getPath() == self::SSE_PATH) {
            $this->handleSse($request, $response);
            return;
        }

        if ($request->getPath() == self::LOOKED_PATH) {
            $this->handleLookup($response, $request->getQuery()['host']);
            return;
        }

        $this->files->then(function (\SplObjectStorage $files) use ($request, $response) {
            foreach ($files as $file) {
                if ($file->getPath() == WEBROOT . $request->getPath()) {
                    $this->handleFile($file, $response);
                    return;
                }
            }

            $this->handleFile($this->filesystem->file(WEBROOT . DIRECTORY_SEPARATOR . '404.txt'), $response);
            return;
        });
    }

    protected function handleFile(File $file, Response $response)
    {
        if (isset($this->filesContents[$file->getPath()])) {
            return $this->filesContents[$file->getPath()];
        }

        $file->getContents()->then(function ($contents) use ($file) {
            $this->filesContents[$file->getPath()] = $contents;

            return $file->close()->then(function () use ($contents) {
                return $contents;
            });
        })->then(function ($fileContents) use ($response) {
            $response->writeHead(200);
            $response->end($fileContents);
        });
    }

    protected function handleLookup(Response $response, $hostName)
    {
        $this->emitter->emit('lookup', $hostName);
        $response->writeHead(200);
        $response->end('{}');
    }

    protected function handleSse(Request $request, Response $response)
    {
        $headers = $request->getHeaders();
        $id = null;isset($headers['Last-Event-ID']) ? $headers['Last-Event-ID'] : null;

        $response->writeHead(200, array('Content-Type' => 'text/event-stream'));
        $this->channel->connect($response, $id);

        $response->on('close', function () use ($response) {
            $this->channel->disconnect($response);
        });
    }
}
