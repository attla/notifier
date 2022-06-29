<?php

namespace Attla\Notifier\Controllers;

use Attla\Jwt;
use Attla\Controller;
use Illuminate\Http\Request;
use Attla\Notifier\Pixel\Queue;

class PixelController extends Controller
{
    private $base64 = 'R0lGODlhAQABAIAAAP///wAAACwAAAAAAQABAAACAkQBADs='; // white gif pixel
    private $encode = 'image/gif';

    protected function response($success = true, $message = null, $code = 200)
    {
        return response()->json(compact(
            'success',
            'message',
        ), $code);
    }

    protected function ok($message = null)
    {
        return $this->response(true, $message);
    }

    protected function fail($message = null, $code = 400)
    {
        return $this->response(false, $message, $code);
    }

    public function unqueue(string $id = null)
    {
        Queue::remove($id);
        return $this->ok('Pixel unqueue successfully.');
    }

    public function tried(string $id = null)
    {
        Queue::tried($id);
        return $this->ok('Pixel attempt registered successfully.');
    }

    public function listen(Request $request)
    {
        $response = response(
            base64_decode($this->base64),
            200,
            ['Content-Type' => $this->encode]
        );

        try {
            $request->p && $request['payload'] = $request->p;
            $this->validate($request, [
                'id' => 'required|string',
                'payload' => 'required|string',
            ]);

            if (
                $className = config('notifier.listeners.' . $request->id)
                and $payload = Jwt::decode($request->payload)
            ) {
                new $className($payload);

                return $response;
            }
        } finally {
            return $response->setStatusCode(400);
        }
    }
}
