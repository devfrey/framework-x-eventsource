<?php

namespace Tests;

use Devfrey\FrameworkX\EventSource\Event;
use PHPUnit\Framework\TestCase;

final class EventTest extends TestCase
{
    /** @test */
    public function consistsOnlyOfComment_returns_true_for_an_event_with_only_a_comment()
    {
        $event = (new Event())->comment('Test');

        self::assertTrue($event->consistsOnlyOfComment());
    }

    /** @test */
    public function consistsOnlyOfComment_returns_false_for_an_event_with_data()
    {
        $event = (new Event())
            ->data('foo')
            ->comment('bar');

        self::assertFalse($event->consistsOnlyOfComment());
    }

    /** @test */
    public function consistsOnlyOfComment_returns_false_for_an_empty_event()
    {
        $event = new Event();

        self::assertFalse($event->consistsOnlyOfComment());
    }
}
