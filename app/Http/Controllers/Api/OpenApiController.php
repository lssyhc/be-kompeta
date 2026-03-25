<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;

class OpenApiController extends Controller
{
    public function __invoke()
    {
        $openApiPath = public_path('docs/swagger/openapi.yaml');

        if (! File::exists($openApiPath)) {
            return response()->json([
                'success' => false,
                'message' => 'OpenAPI spec tidak ditemukan.',
                'data' => null,
                'errors' => null,
                'meta' => null,
            ], 404);
        }

        return response()->file($openApiPath, [
            'Content-Type' => 'application/yaml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=300',
        ]);
    }
}
