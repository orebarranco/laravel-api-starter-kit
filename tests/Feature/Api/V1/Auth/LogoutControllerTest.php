<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Auth;

use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Symfony\Component\HttpFoundation\Response;

beforeEach(function (): void {
    $this->endpoint = '/api/v1/auth/logout';
    $this->user = User::factory()->create();
});

it('logs out an authenticated user successfully', function (): void {
    Sanctum::actingAs($this->user);

    $response = $this->postJson($this->endpoint);

    $response->assertStatus(Response::HTTP_OK)
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', __('Logged out successfully'));

    expect($this->user->tokens()->count())->toBe(0);
});

it('fails to logout if not authenticated', function (): void {
    $response = $this->postJson($this->endpoint);

    $response->assertStatus(Response::HTTP_UNAUTHORIZED);
});
