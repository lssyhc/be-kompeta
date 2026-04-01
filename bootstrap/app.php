<?php

use App\Http\Controllers\StorageProxyController;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            if (config('filesystems.disks.public.driver') === 's3') {
                Route::get('/storage/{path}', StorageProxyController::class)
                    ->where('path', '.*');
            }
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(function (Request $request, Throwable $e) {
            return $request->expectsJson() || $request->is('api/*');
        });

        $exceptions->render(function (ValidationException $e, Request $request) {
            if (! ($request->expectsJson() || $request->is('api/*'))) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'data' => null,
                'errors' => $e->errors(),
                'meta' => null,
            ], 422);
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if (! ($request->expectsJson() || $request->is('api/*'))) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
                'data' => null,
                'errors' => null,
                'meta' => null,
            ], 401);
        });

        $exceptions->render(function (Throwable $e, Request $request) {
            if (! ($request->expectsJson() || $request->is('api/*'))) {
                return null;
            }

            $status = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500;
            $message = $status >= 500
                ? 'Terjadi kesalahan pada server.'
                : ($e->getMessage() ?: 'Terjadi kesalahan.');

            return response()->json([
                'success' => false,
                'message' => $message,
                'data' => null,
                'errors' => null,
                'meta' => null,
            ], $status);
        });
    })->create();
