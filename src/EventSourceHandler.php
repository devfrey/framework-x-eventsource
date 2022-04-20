<?php

namespace Devfrey\FrameworkX\EventSource;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use React\EventLoop\Loop;
use React\EventLoop\TimerInterface;
use React\Http\Message\Response;
use React\Stream\ThroughStream;
use React\Stream\WritableStreamInterface;

class EventSourceHandler
{
    /**
     * @param  \Devfrey\FrameworkX\EventSource\BufferedEventStream  $eventStream
     * @param  float|false  $keepAliveInterval  Keep-alive interval in seconds. Set to false to disable keep-alive.
     */
    public function __construct(
        protected BufferedEventStream $eventStream,
        float|bool $keepAliveInterval = 15.0,
    ) {
        $this->setupKeepAlive($keepAliveInterval);
    }

    public function __invoke(RequestInterface $request): ResponseInterface
    {
        $this->handleRequest(
            $stream = new ThroughStream(),
            $request->getHeaderLine('Last-Event-ID')
        );

        return new Response(
            headers: ['Content-Type' => 'text/event-stream'],
            body: $stream,
        );
    }

    protected function handleRequest(WritableStreamInterface $stream, string $lastEventId): void
    {
        Loop::get()->futureTick(function () use ($stream, $lastEventId) {
            $this->handleConnect($stream, $lastEventId);
        });

        $stream->on('close', fn () => $this->handleDisconnect($stream));
    }

    /**
     * @param  float|false  $interval  Set to false to disable keep-alive
     * @return \React\EventLoop\TimerInterface|null
     */
    protected function setupKeepAlive(float|bool $interval): ?TimerInterface
    {
        if ($interval === false) {
            return null;
        }

        return Loop::get()->addPeriodicTimer($interval, fn () => $this->handleKeepAlive());
    }

    protected function handleConnect(WritableStreamInterface $stream, string $lastEventId): void
    {
        $this->eventStream->connect($stream, $lastEventId);
    }

    protected function handleDisconnect(WritableStreamInterface $stream): void
    {
        $this->eventStream->disconnect($stream);
    }

    /**
     * Send a comment with a random hash to connected clients to keep the connection alive.
     *
     * @return void
     */
    protected function handleKeepAlive(): void
    {
        $this->eventStream->send(
            (new Event())->comment(sha1(mt_rand()))
        );
    }
}

