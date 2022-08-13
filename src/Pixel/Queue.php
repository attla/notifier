<?php

namespace Attla\Notifier\Pixel;

use Attla\Cookier\Facade as Cookier;
use Attla\DataToken\Facade as DataToken;
use Illuminate\Support\Facades\Cookie;

class Queue
{
    /**
     * Queue of pixels
     *
     * @var QueueItem[]
     */
    private static $queue = [];

    /**
     * Load queued pixels from cookie
     *
     * @return void
     */
    public static function load()
    {
        static::$queue = collect(Cookier::get('notifier', []))
            ->map(fn($pixel) => new QueueItem($pixel))
            ->all();
    }

    /**
     * Add a pixel to queue
     *
     * @param string $id
     * @param string $pixel
     * @param int $tries
     * @param int $backoff
     * @return void
     */
    public static function add(
        string $id,
        string $pixel,
        int $tries,
        int $backoff
    ) {
        static::$queue[$id] =  new QueueItem(compact(
            'pixel',
            'tries',
            'backoff'
        ));
    }

    /**
     * Check if pixel exists in queue
     *
     * @param string $id
     * @return bool
     */
    public static function has(string $id)
    {
        return isset(static::$queue[$id]);
    }

    /**
     * Remove queued pixel
     *
     * @param string $id
     * @return void
     */
    public static function remove(string $id)
    {
        if (static::has($id)) {
            unset(static::$queue[$id]);
        }
    }

    /**
     * Get all queued pixels
     *
     * @return QueueItem[]
     */
    public static function all()
    {
        return static::$queue;
    }

    /**
     * Subtract the pixel attempt
     *
     * @param string $id
     * @return void
     */
    public static function tried(string $id)
    {
        if ($pixel = static::$queue[$id] ?? null) {
            $pixel->tries--;
            $pixel->next = static::next($pixel->backoff);
            static::$queue[$id] = $pixel;
        }
    }

    /**
     * Get pixel next attempt
     *
     * @param int $backoff
     * @return int
     */
    public static function next(int $backoff)
    {
        return time() + ($backoff * 60);
    }

    /**
     * Get pixels available
     *
     * @return \Illuminate\Support\Collection
     */
    private static function getAvailable()
    {
        return collect(static::$queue)
            ->filter(fn(QueueItem $pixel) => $pixel->tries);
    }

    /**
     * Get all queued pixels
     *
     * @return array
     */
    public static function notifiables()
    {
        return static::getAvailable()
            ->filter(fn(QueueItem $pixel) => $pixel->next <= time())
            ->map(fn(QueueItem $pixel) => $pixel->pixel)
            ->toArray();
    }

    /**
     * Get queued pixels cookie
     *
     * @return void
     */
    public static function cookie()
    {
        $name = 'notifier';

        return ($queue = static::getAvailable()->toArray())
            ? Cookier::set(
                $name,
                DataToken::payload($queue)
                    ->encode(),
                525600
            )
            : Cookie::forget(Cookier::withPrefix($name));
    }
}
