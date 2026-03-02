<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Actions\Auth\LoginUserAction;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Resources\Api\V1\Auth\UserResource;
use App\Traits\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class LoginController
{
    use ApiResponse;

    public function __invoke(LoginRequest $request, LoginUserAction $action): JsonResponse
    {
        try {
            $result = $action->execute($request->toDto());

            return $this->success(
                data: [
                    'user' => new UserResource($result['user']),
                    'token' => $result['token'],
                ],
                message: 'Login successful'
            );
        } catch (AuthenticationException $authenticationException) {
            return $this->error(
                message: $authenticationException->getMessage(),
                code: 'UNAUTHORIZED',
                status: Response::HTTP_UNAUTHORIZED
            );
        }
    }
}
