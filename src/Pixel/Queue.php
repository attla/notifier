<?php

namespace Attla\Notifier\Pixel;

use Attla\Jwt;
use Attla\Cookier;
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
     * Remove queued pixel
     *
     * @param string $id
     * @return void
     */
    public static function remove(string $id)
    {
        unset(static::$queue[$id]);
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
            $pixel->tries = $pixel->tries - 1;
            $pixel->next = time() + ($pixel->backoff * 60);
            static::$queue[$id] = $pixel;
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
                Jwt::payload($queue)
                    ->encode(),
                525600
            )
            : Cookie::forget(Cookier::withPrefix($name));
    }

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
}
