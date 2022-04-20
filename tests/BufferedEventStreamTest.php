<?php

namespace Tests;

use Devfrey\FrameworkX\EventSource\BufferedEventStream;
use Devfrey\FrameworkX\EventSource\Encoder;
use Devfrey\FrameworkX\EventSource\Event;
use PHPUnit\Framework\TestCase;
use React\Stream\WritableStreamInterface;

final class BufferedEventStreamTest extends TestCase
{
    /** @test */
    public function connect_sends_buffered_messages_to_the_stream()
    {
        // Arrange
        $encoder = $this->createMock(Encoder::class);
        $events = new BufferedEventStream($encoder);
        $stream = $this->createMock(WritableStreamInterface::class);

        $firstEvent = new Event('first');
        $secondEvent = new Event('second');

        // Expect
        $stream->expects(self::once())
            ->method('write')
            ->with("event: second\n\n");

        $encoder
            ->expects(self::exactly(3))
            ->method('__invoke')
            ->withConsecutive(
                // $events->send($firstEvent):
                [self::equalTo(''), self::equalTo('first'), self::equalTo(''), self::equalTo('1'), self::equalTo(null)],
                // $events->send($secondEvent):
                [self::equalTo(''), self::equalTo('second'), self::equalTo(''), self::equalTo('2'), self::equalTo(null)],
                // $events->connect() -> re-encode the $secondEvent from the buffer
                [self::equalTo(''), self::equalTo('second'), self::equalTo(''), self::equalTo('2'), self::equalTo(null)]
            )
            ->willReturnOnConsecutiveCalls(
                "event: first\n\n",
                "event: first\n\n",
                "event: second\n\n"
            );

        // Act
        $events->send($firstEvent);
        // a "disconnect" happened between these events
        $events->send($secondEvent);

        $events->connect($stream, '1'); // "reconnect"
    }

    /** @test */
    public function send_does_not_buffer_comments()
    {
        // Arrange
        $events = new BufferedEventStream(new Encoder());
        $comment = (new Event())->comment('keep-alive-comment');

        // Act
        $events->send($comment);

        // Assert
        self::assertEquals(0, $events->bufferCount());
    }

    /** @test */
    public function bufferCount_returns_0_for_new_instance()
    {
        self::assertEquals(0, (new BufferedEventStream())->bufferCount());
    }

    /** @test */
    public function bufferCount_returns_1_after_sending_an_event()
    {
        // Arrange
        $events = new BufferedEventStream();
        $event = (new Event())->data('test');

        // Act
        $events->send($event);

        // Assert
        self::assertEquals(1, $events->bufferCount());
    }
}
