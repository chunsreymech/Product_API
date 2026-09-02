<?php

namespace App\Traits;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

trait ApiResponse
{
    /**
     * Return a standardized success JSON response.
     */
    protected function success(mixed $data = [], string $message = 'Success', int $status = 200, array $meta = []): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];

        if (!empty($meta)) {
            $response['meta'] = $meta;
        }

        return response()->json($response, $status);
    }

    /**
     * Return a standardized paginated JSON response.
     */
    protected function paginated(LengthAwarePaginator|AnonymousResourceCollection $paginator, string $message = 'Data retrieved successfully', int $status = 200): JsonResponse
    {
        if ($paginator instanceof AnonymousResourceCollection && $paginator->resource instanceof LengthAwarePaginator) {
            $pagination = $paginator->resource;
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $paginator->resolve(),
                'meta' => [
                    'current_page' => $pagination->currentPage(),
                    'last_page' => $pagination->lastPage(),
                    'per_page' => $pagination->perPage(),
                    'total' => $pagination->total(),
                ],
            ], $status);
        }

        if ($paginator instanceof LengthAwarePaginator) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $paginator->items(),
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                ],
            ], $status);
        }

        return $this->success($paginator, $message, $status);
    }

    /**
     * Return a standardized error JSON response.
     */
    protected function error(string $message = 'An error occurred', mixed $errors = [], int $status = 400): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status);
    }

    /**
     * Return a 204 No Content response.
     */
    protected function noContent(): JsonResponse
    {
        return response()->json(null, 204);
    }
}
