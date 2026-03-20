<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;

abstract class Controller
{
    protected function successResponse(mixed $data = null, string $message = 'Success', int $status = 200, ?array $meta = null): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'errors' => null,
            'meta' => $meta,
        ], $status);
    }

    protected function errorResponse(string $message = 'Error', int $status = 400, mixed $errors = null, ?array $meta = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => null,
            'errors' => $errors,
            'meta' => $meta,
        ], $status);
    }

    protected function paginatedResponse(LengthAwarePaginator $paginator, string $message = 'Data berhasil diambil.'): JsonResponse
    {
        return $this->successResponse(
            $paginator->items(),
            $message,
            200,
            [
                'pagination' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'has_more_pages' => $paginator->hasMorePages(),
                ],
            ]
        );
    }
}
