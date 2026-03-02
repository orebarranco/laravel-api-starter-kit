<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Actions\Auth\LogoutAction;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class LogoutController
{
    use ApiResponse;

    public function __invoke(Request $request, LogoutAction $logoutAction): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $logoutAction->execute($user);

        return $this->success(
            data: null,
            message: 'Logged out successfully'
        );
    }
}
