<?php

namespace Attla\Notifier\Pixel;

class Injector
{
    /**
     * Append script to html
     * I dont care it doesn't place before </body> tag or not.
     *
     * @param string $content
     * @return string
     */
    public static function pixelQueue($content)
    {
        return $content . view('notifier::pixel-queue', [
            'pixels' => Queue::notifiables()
        ])->getContent();
    }
}
