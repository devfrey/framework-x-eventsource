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
     * @param  array<string>  $comments
     * @param  string|null  $event
     * @param  string  $data
     * @param  string  $id
     * @param  int|null  $retry
     * @return string
     */
    public function __invoke(
        array $comments,
        ?string $event,
        string $data,
        string $id,
        ?int $retry
    ): string {
        $encoded = '';

        foreach ($comments as $comment) {
            $encoded .= ":{$comment}\n";
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
     * Line breaks within a data field are not permitted, because they would indicate the end of a field (or even a
     * block). Therefore every line break should result in an additional data field.
     *
     * @param  string  $data
     * @return array
     */
    protected function explodeLineBreaks(string $data): array
    {
        if ($data === '') {
            return [];
        }

        return preg_split('/\R/', $data);
    }
}
