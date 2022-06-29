<?php

namespace Attla\Notifier\Pixel;

use Illuminate\Contracts\Support\{
    Jsonable,
    Arrayable
};

class QueueItem extends \ArrayObject implements
    Arrayable,
    Jsonable,
    \JsonSerializable
{
    use \Attla\AbstractData;

    /**
     * The pixel url
     *
     * @var string
     */
    public string $pixel;

    /**
     * The pixel attempts
     *
     * @var int
     */
    public int $tries;

    /**
     * How many minutes there should be between pixel notifier attempts
     *
     * @var int
     */
    public int $backoff;

    /**
     * Timestamp of the next attempt
     *
     * @var int
     */
    public int $next = 0;
}
