<?php

namespace Attla\Notifier\Middlewares;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Attla\Notifier\Pixel\Queue;
use Illuminate\Http\JsonResponse;
use Attla\Notifier\Pixel\Injector;

class InjectPixelNotifier
{
    /**
     * Handle an incoming request
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, \Closure $next)
    {
        $response = $next($request);
        Queue::store();

        if (
            $request->getMethod() === Request::METHOD_GET &&
            Str::startsWith($response->headers->get('Content-Type', ''), 'text/html') &&
            !$request->isXmlHttpRequest() &&
            !$response instanceof JsonResponse
        ) {
            $response->setContent(
                Injector::pixelQueue($response->getContent())
            );
        }

        return $response;
    }
}
