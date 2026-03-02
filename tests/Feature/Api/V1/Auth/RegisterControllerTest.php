<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Auth;

use App\Models\User;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

beforeEach(function (): void {
    $this->endpoint = '/api/v1/auth/register';
    $this->validPayload = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ];
});

it('registers a new user successfully', function (): void {
    $response = $this->postJson($this->endpoint, $this->validPayload);

    $response->assertStatus(Response::HTTP_CREATED)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.user.name', $this->validPayload['name'])
        ->assertJsonPath('data.user.email', $this->validPayload['email']);

    expect($response->json('data.token'))->toBeString()->not->toBeEmpty();

    $this->assertDatabaseHas('users', [
        'email' => $this->validPayload['email'],
    ]);
});

it('fails registration with invalid data', function (array $payload, string|array $errors): void {
    $this->postJson($this->endpoint, array_merge($this->validPayload, $payload))
        ->assertUnprocessable()
        ->assertJsonValidationErrors($errors);
})->with([
    'missing name' => [['name' => ''], 'name'],
    'name too long' => [['name' => Str::random(256)], 'name'],
    'missing email' => [['email' => ''], 'email'],
    'invalid email' => [['email' => 'not-an-email'], 'email'],
    'email too long' => [['email' => Str::random(250).'@example.com'], 'email'],
    'missing password' => [['password' => ''], 'password'],
    'password too short' => [['password' => 'Short1!'], 'password'],
    'password confirmation mismatch' => [['password_confirmation' => 'different'], 'password'],
]);

it('fails registration if email is already taken', function (): void {
    User::factory()->create(['email' => 'taken@example.com']);

    $this->postJson($this->endpoint, array_merge($this->validPayload, ['email' => 'taken@example.com']))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('email');
});

it('does not include sensitive information in response', function (): void {
    $response = $this->postJson($this->endpoint, $this->validPayload);

    $response->assertStatus(Response::HTTP_CREATED);

    expect($response->json('data.user'))->not->toHaveKeys(['password', 'remember_token']);
});
