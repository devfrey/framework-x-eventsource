<?php

namespace Devfrey\FrameworkX\EventSource;

/**
 * @see https://html.spec.whatwg.org/multipage/server-sent-events.html
 */
final class Event
{
    private string $comment = '';
    private string $data = '';
    private string $id = '';
    private ?int $retry = null;

    public function __construct(
        private ?string $event = null,
    ) {
    }

    /**
     * Set the event comment, supports line breaks.
     * Do not prefix comments with a colon – this is done by the encoder.
     *
     * @param  string  $comment
     * @return self
     */
    public function comment(string $comment): self
    {
        $clone = clone $this;

        $clone->comment = $comment;

        return $clone;
    }

    /**
     * Set the event data, supports line breaks.
     *
     * @param  string  $data
     * @return $this
     */
    public function data(string $data): self
    {
        $clone = clone $this;

        $clone->data = $data;

        return $clone;
    }

    /**
     * Set the event ID.
     *
     * @param  string  $id
     * @return $this
     */
    public function id(string $id): self
    {
        $clone = clone $this;

        $clone->id = $id;

        return $clone;
    }

    /**
     *
     * @param  int|null  $retry
     * @return $this
     */
    public function retry(?int $retry): self
    {
        $clone = clone $this;

        $clone->retry = $retry;

        return $clone;
    }

    /**
     * Encode this event using the given encoder.
     *
     * @param  \Devfrey\FrameworkX\EventSource\Encoder  $encoder
     * @return string
     */
    public function toString(Encoder $encoder): string
    {
        return $encoder(
            $this->comment,
            $this->event,
            $this->data,
            $this->id,
            $this->retry,
        );
    }

    /**
     * Determine if this event consists only of a comment.
     *
     * @return bool
     */
    public function consistsOnlyOfComment(): bool
    {
        return is_null($this->event)
            && is_null($this->retry)
            && $this->data === ''
            && $this->comment !== '';
    }
}
