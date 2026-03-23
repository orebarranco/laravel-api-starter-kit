<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

beforeEach(function (): void {
    $this->endpoint = '/api/v1/auth/login';
    $this->password = 'Password123!';
    $this->user = User::factory()->create([
        'password' => Hash::make($this->password),
    ]);
});

it('logs in a user successfully with valid credentials', function (): void {
    $payload = [
        'email' => $this->user->email,
        'password' => $this->password,
    ];

    $response = $this->postJson($this->endpoint, $payload);

    $response->assertStatus(Response::HTTP_OK)
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Login successful')
        ->assertJsonPath('data.user.email', $this->user->email);

    expect($response->json('data.token'))->toBeString()->not->toBeEmpty();
});

it('fails login with invalid credentials', function (): void {
    $payload = [
        'email' => $this->user->email,
        'password' => 'wrong-password',
    ];

    $response = $this->postJson($this->endpoint, $payload);

    $response->assertStatus(Response::HTTP_UNAUTHORIZED)
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'The provided credentials are incorrect.')
        ->assertJsonPath('error.code', 'INVALID_CREDENTIALS');
});

it('fails login with non-existent email', function (): void {
    $payload = [
        'email' => 'nonexistent@example.com',
        'password' => $this->password,
    ];

    $response = $this->postJson($this->endpoint, $payload);

    $response->assertStatus(Response::HTTP_UNAUTHORIZED)
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'The provided credentials are incorrect.');
});

it('fails login with validation errors', function (array $payload, string $field): void {
    $this->postJson($this->endpoint, $payload)
        ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJsonValidationErrors($field, 'error.errors');
})->with([
    'missing email' => [['email' => '', 'password' => 'password'], 'email'],
    'invalid email' => [['email' => 'not-an-email', 'password' => 'password'], 'email'],
    'missing password' => [['email' => 'test@example.com', 'password' => ''], 'password'],
]);
