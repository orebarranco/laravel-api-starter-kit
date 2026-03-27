<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\JsonApi\AnonymousResourceCollection as JsonApiResourceCollection;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;

trait ApiResponse
{
    /**
     * @return array{version: mixed, timestamp: string}
     */
    public function baseMeta(): array
    {
        return [
            'version' => request()->attributes->get('api_version', 'v1'),
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    protected function success(mixed $data, string $message = 'OK', int $status = Response::HTTP_OK, array $meta = []): JsonResponse
    {
        if ($data instanceof JsonApiResource) {
            $data = $data->resolve()['data'];
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'meta' => array_merge($this->baseMeta(), $meta),
        ], $status);
    }

    protected function successCollection(AnonymousResourceCollection $collection, string $message = 'OK'): JsonResponse
    {
        /** @var LengthAwarePaginator<int, mixed> $paginator */
        $paginator = $collection->resource;

        if ($collection instanceof JsonApiResourceCollection) {
            /** @var Collection<int, JsonApiResource> $items */
            $items = $collection->collection;
            $data = $items->map(fn (JsonApiResource $resource) => $resource->resolve()['data'])->values()->all();
        } else {
            $data = $collection->toArray(request());
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'meta' => array_merge($this->baseMeta(), [
                'pagination' => [
                    'total' => $paginator->total(),
                    'per_page' => $paginator->perPage(),
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'from' => $paginator->firstItem(),
                    'to' => $paginator->lastItem(),
                ],
            ]),
            'links' => [
                'self' => $paginator->url($paginator->currentPage()),
                'first' => $paginator->url(1),
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
                'last' => $paginator->url($paginator->lastPage()),
            ],
        ]);
    }

    /**
     * @param  array<string, mixed>  $errors
     */
    protected function validationError(array $errors, string $message = 'The given data was invalid.'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'error' => [
                'code' => 'VALIDATION_ERROR',
                'detail' => 'Check the errors field for more information.',
                'errors' => $errors,
            ],
            'meta' => $this->baseMeta(),
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    protected function error(string $message, string $code, string $detail = '', int $status = Response::HTTP_BAD_REQUEST): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'error' => [
                'code' => $code,
                'detail' => $detail,
            ],
            'meta' => $this->baseMeta(),
        ], $status);
    }
}
