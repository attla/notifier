<?php

return [
    // A path for receiving notifications.
    'path' => '/notify',
    'server' => [
        // Default endpoint to send notifications.
        'endpoint' => 'http://localhost/notify',
        // Default secret to encrypt payload.
        'secret' => 'do androids dream of electric sheep?',
    ],
    // The amount of times should be called before we give up.
    'tries' => 3,
    // This determines how many minutes there should be between attempts.
    'backoff' => 20,
    // List of listeners to receive notifications
    'listeners' => [
        // 'pixel-id' => ListenerExample::class,
    ],
];
