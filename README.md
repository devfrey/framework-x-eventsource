# EventSource / Server-Sent Events for [Framework X](https://github.com/clue/framework-x)
This package is an experimental implementation of Server-Sent Events for `clue/framework-x`. It doesn't require any modifications to the framework.

Feedback is welcome. Hoping to work towards an implementation that can be shipped with Framework X.

**Example:**
```php
<?php

use Devfrey\FrameworkX\EventSource\BufferedEventStream;
use Devfrey\FrameworkX\EventSource\Event;
use Devfrey\FrameworkX\EventSource\EventSourceHandler;

require __DIR__ . '/vendor/autoload.php';

$loop = React\EventLoop\Factory::create();
$app = new FrameworkX\App($loop);

$app->get('/', new EventSourceHandler($loop, $events = new BufferedEventStream()));

// Send a random value every second
$loop->addPeriodicTimer(1.0, function () use ($events) {
    $events->send((new Event())->data(mt_rand()));
});

$app->run();
$loop->run();
```
