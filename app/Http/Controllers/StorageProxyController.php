<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class StorageProxyController extends Controller
{
    public function __invoke(string $path): Response
    {
        $disk = Storage::disk('public');

        if (! $disk->exists($path)) {
            abort(404);
        }

        return $disk->response($path, null, [
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
