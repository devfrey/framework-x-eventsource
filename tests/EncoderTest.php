<?php

namespace Tests;

use Devfrey\FrameworkX\EventSource\Encoder;
use Devfrey\FrameworkX\EventSource\Event;
use PHPUnit\Framework\TestCase;

final class EncoderTest extends TestCase
{
    /** @test */
    public function data_with_multiple_lines()
    {
        $event = (new Event())->data("Multi\nlines\r\nare\rsupported\n");

        self::assertEquals(
            "data: Multi\n" .
            "data: lines\n" .
            "data: are\n" .
            "data: supported\n" .
            "data: \n\n",
            $event->toString(new Encoder())
        );
    }

    /** @test */
    public function event_with_data()
    {
        $event = (new Event('my-event'))
            ->data('test');

        self::assertEquals(
            "event: my-event\n" .
            "data: test\n\n",
            $event->toString(new Encoder())
        );
    }

    /** @test */
    public function retry()
    {
        $event = (new Event())->retry(5);

        self::assertEquals(
            "retry: 5\n\n",
            $event->toString(new Encoder())
        );
    }

    /** @test */
    public function id()
    {
        $event = (new Event())
            ->id('5')
            ->data('+1');

        self::assertEquals(
            "data: +1\n" .
            "id: 5\n\n",
            $event->toString(new Encoder())
        );
    }

    /** @test */
    public function id_omitted_when_empty_string()
    {
        $event = (new Event())
            ->id('')
            ->data('test');

        self::assertEquals("data: test\n\n", $event->toString(new Encoder()));
    }

    /** @test */
    public function single_comment()
    {
        $event = (new Event())->comment('keep-alive');

        self::assertEquals(
            ":keep-alive\n\n",
            $event->toString(new Encoder())
        );
    }

    /** @test */
    public function multiple_comments()
    {
        $event = (new Event())->comment(
            'This is a comment',
            'And this too.'
        );

        self::assertEquals(
            ":This is a comment\n" .
            ":And this too.\n\n",
            $event->toString(new Encoder())
        );
    }

    /** @test */
    public function data_with_multi_line_json()
    {
        $event = (new Event())->data(
            "{\n" .
            '"key": "value"' . "\n" .
            '}'
        );

        self::assertEquals(
            "data: {\n" .
            'data: "key": "value"' . "\n" .
            "data: }\n\n",
            $event->toString(new Encoder())
        );
    }
}
