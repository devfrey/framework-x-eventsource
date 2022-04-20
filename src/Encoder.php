<?php

namespace Devfrey\FrameworkX\EventSource;

/**
 * @see https://html.spec.whatwg.org/multipage/server-sent-events.html#event-stream-interpretation
 */
class Encoder
{
    /**
     * Encode given fields into an event.
     *
     * @param  string  $comment
     * @param  string|null  $event
     * @param  string  $data
     * @param  string  $id
     * @param  int|null  $retry
     * @return string
     */
    public function __invoke(
        string $comment,
        ?string $event,
        string $data,
        string $id,
        ?int $retry,
    ): string {
        $encoded = '';

        foreach ($this->explodeLineBreaks($comment) as $line) {
            $encoded .= ":{$line}\n";
        }

        if (! is_null($event)) {
            $encoded .= "event: {$event}\n";
        }

        foreach ($this->explodeLineBreaks($data) as $datum) {
            $encoded .= "data: {$datum}\n";
        }

        if ($id !== '') {
            $encoded .= "id: {$id}\n";
        }

        if ($retry !== null) {
            $encoded .= "retry: {$retry}\n";
        }

        return $encoded . "\n";
    }

    /**
     * Line breaks within comments and data fields are not permitted, because
     * they would indicate the end of a field (or even a block). Therefore every
     * line break should result in an additional comment or data field.
     *
     * @param  string  $data
     * @return string[]
     */
    protected function explodeLineBreaks(string $data): array
    {
        if ($data === '') {
            return [];
        }

        return preg_split('/\R/', $data);
    }
}
