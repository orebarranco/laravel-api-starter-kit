<?php

declare(strict_types=1);

namespace Tests\Unit\Traits;

use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;

beforeEach(function (): void {
    $this->subject = new class
    {
        use ApiResponse;

        public function callSuccessCollection(AnonymousResourceCollection $collection, string $message = 'OK'): JsonResponse
        {
            return $this->successCollection($collection, $message);
        }
    };
});

it('successCollection returns paginated response with correct structure', function (): void {
    $items = Collection::make([['id' => 1], ['id' => 2], ['id' => 3]]);
    $paginator = new LengthAwarePaginator($items, 10, 3, 1, ['path' => 'http://localhost/api/v1/items']);
    $collection = AnonymousResourceCollection::make($paginator, JsonResource::class);

    $response = $this->subject->callSuccessCollection($collection, 'Listed successfully');

    expect($response->status())->toBe(Response::HTTP_OK)
        ->and($response->getData(true)['success'])->toBeTrue()
        ->and($response->getData(true)['message'])->toBe('Listed successfully')
        ->and($response->getData(true)['meta']['pagination'])->toMatchArray([
            'total' => 10,
            'per_page' => 3,
            'current_page' => 1,
            'last_page' => 4,
        ])
        ->and($response->getData(true)['links'])->toHaveKeys(['self', 'first', 'prev', 'next', 'last']);
});

it('successCollection uses default message when none provided', function (): void {
    $items = Collection::make([]);
    $paginator = new LengthAwarePaginator($items, 0, 15, 1, ['path' => 'http://localhost/api/v1/items']);
    $collection = AnonymousResourceCollection::make($paginator, JsonResource::class);

    $response = $this->subject->callSuccessCollection($collection);

    expect($response->getData(true)['message'])->toBe('OK');
});
