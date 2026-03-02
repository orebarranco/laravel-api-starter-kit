<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Resources\Api\V1\Auth\UserResource;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class MeController
{
    use ApiResponse;

    public function __invoke(Request $request): JsonResponse
    {
        return $this->success(
            data: [
                'user' => new UserResource($request->user()),
            ],
            message: 'User profile retrieved'
        );
    }
}
