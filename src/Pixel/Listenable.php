<?php

namespace Attla\Notifier\Pixel;

interface Listenable
{
    /**
     * Create a new listener instance
     *
     * @return void
     */
    public function __construct(array|object $payload);
}
