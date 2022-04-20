<?php

namespace Devfrey\FrameworkX\EventSource;

use React\Stream\WritableStreamInterface;

class BufferedEventStream
{
    protected int $lastEventId = 0;

    /** @var \SplObjectStorage<\React\Stream\WritableStreamInterface> */
    protected \SplObjectStorage $streams;

    /** @var \Devfrey\FrameworkX\EventSource\Encoder */
    protected Encoder $encoder;

    /** @var array<int, \Devfrey\FrameworkX\EventSource\Event> */
    protected array $buffer = [];

    /**
     * Create the buffered event stream.
     *
     * @param  \Devfrey\FrameworkX\EventSource\Encoder|null  $encoder
     * @return void
     */
    public function __construct(?Encoder $encoder = null)
    {
        $this->encoder = $encoder ?? new Encoder();
        $this->streams = new \SplObjectStorage();
    }

    /**
     * Connect a client (stream) and (optionally) catch up on events from the buffer.
     *
     * @param  \React\Stream\WritableStreamInterface  $stream
     * @param  string  $lastEventId
     * @return void
     */
    public function connect(WritableStreamInterface $stream, string $lastEventId): void
    {
        $this->streams->attach($stream);

        if (is_numeric($lastEventId)) {
            $this->catchUpFromBuffer((int) $lastEventId, $stream);
        }
    }

    /**
     * Disconnected a client (stream).
     *
     * @param  \React\Stream\WritableStreamInterface  $stream
     * @return void
     */
    public function disconnect(WritableStreamInterface $stream): void
    {
        $this->streams->detach($stream);
    }

    /**
     * Send an event to connected clients.
     *
     * @param  \Devfrey\FrameworkX\EventSource\Event  $event
     * @return void
     */
    public function send(Event $event): void
    {
        // Skip buffering events that only consist of a comment, such as the
        // keep-alive event.
        if (! $event->consistsOnlyOfComment()) {
            $this->lastEventId++;

            $event = $event->id($this->lastEventId);

            $this->buffer[$this->lastEventId] = $event;
        }

        $encoded = $event->toString($this->encoder);

        foreach ($this->streams as $stream) {
            $stream->write($encoded);
        }
    }

    /**
     * Get the current size of the buffer.
     *
     * @return int
     */
    public function bufferCount(): int
    {
        return count($this->buffer);
    }

    /**
     * Send all buffered events since the last event ID.
     *
     * @param  int  $lastEventId
     * @param  \React\Stream\WritableStreamInterface  $stream
     * @return void
     */
    protected function catchUpFromBuffer(int $lastEventId, WritableStreamInterface $stream): void
    {
        for ($i = $lastEventId + 1; isset($this->buffer[$i]); $i++) {
            $stream->write(
                $this->buffer[$i]->toString($this->encoder),
            );
        }
    }
}
