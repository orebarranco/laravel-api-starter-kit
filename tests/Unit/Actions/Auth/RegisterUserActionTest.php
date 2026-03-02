<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Auth;

use App\Actions\Auth\RegisterUserAction;
use App\DTOs\Auth\RegisterUserDTO;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

beforeEach(function (): void {
    $this->action = new RegisterUserAction();
});

it('creates a user and returns user with a valid token', function (): void {
    $userData = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
    ];

    $dto = new RegisterUserDTO(...$userData);

    $result = $this->action->execute($dto);

    expect($result)->toBeArray()
        ->toHaveKeys(['user', 'token'])
        ->and($result['user'])->toBeInstanceOf(User::class)
        ->and($result['user']->name)->toBe($userData['name'])
        ->and($result['user']->email)->toBe($userData['email'])
        ->and($result['token'])->toBeString()->not->toBeEmpty()
        ->and($result['token'])->toMatch('/^\d+\|[\w\W]+$/');

    $this->assertDatabaseHas('users', [
        'email' => $userData['email'],
    ]);

    expect(Hash::check($userData['password'], $result['user']->password))->toBeTrue();
});

it('persists user with all required attributes', function (): void {
    $dto = new RegisterUserDTO(
        name: 'Jane Doe',
        email: 'jane@example.com',
        password: 'password123',
    );

    $result = $this->action->execute($dto);
    $user = $result['user'];

    expect($user->exists)->toBeTrue()
        ->and($user->id)->not->toBeNull()
        ->and($user->created_at)->not->toBeNull()
        ->and($user->updated_at)->not->toBeNull();
});
