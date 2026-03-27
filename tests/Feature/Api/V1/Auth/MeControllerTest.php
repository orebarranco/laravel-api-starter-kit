<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Auth;

use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Symfony\Component\HttpFoundation\Response;

beforeEach(function (): void {
    $this->endpoint = '/api/v1/auth/me';
    $this->user = User::factory()->create();
});

it('retrieves the authenticated user profile', function (): void {
    Sanctum::actingAs($this->user);

    $response = $this->getJson($this->endpoint);

    $response->assertStatus(Response::HTTP_OK)
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', __('User profile retrieved'))
        ->assertJsonPath('data.id', $this->user->id)
        ->assertJsonPath('data.attributes.email', $this->user->email);
});

it('fails to retrieve profile if not authenticated', function (): void {
    $response = $this->getJson($this->endpoint);

    $response->assertStatus(Response::HTTP_UNAUTHORIZED);
});
