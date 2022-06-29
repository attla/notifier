<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'as'            => 'notifier.',
    'prefix'        => config('notifier.path', '/notifier'),
    'namespace'     => 'Attla\\Notifier\\Controllers',
    'middleware'    => [
        'web',
    ],
], function () {
    Route::group([
        'as'         => 'pixel.',
        'prefix'     => '/pixel',
        'controller' => 'PixelController',
    ], function () {
        Route::name('tried')->get('/tried/{id?}', 'tried');
        Route::name('unqueue')->get('/unqueue/{id?}', 'unqueue');
        Route::name('listen')->get('/', 'listen');
    });
});
