<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Exceptions\ApiExceptionHandler;
use App\Exceptions\Auth\InvalidCredentialsException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

// --- Feature tests via HTTP ---

it('returns 404 with standard error format for unknown routes', function (): void {
    $this->getJson('/api/v1/nonexistent-route')
        ->assertNotFound()
        ->assertJsonStructure(['success', 'message', 'error' => ['code', 'detail'], 'meta'])
        ->assertJsonPath('success', false)
        ->assertJsonPath('error.code', 'NOT_FOUND');
});

it('returns 401 with standard error format when unauthenticated', function (): void {
    $this->getJson('/api/v1/auth/me')
        ->assertUnauthorized()
        ->assertJsonPath('success', false)
        ->assertJsonPath('error.code', 'UNAUTHENTICATED');
});

it('returns 422 with field-level errors on validation failure', function (): void {
    $this->postJson('/api/v1/auth/login', [])
        ->assertUnprocessable()
        ->assertJsonPath('success', false)
        ->assertJsonPath('error.code', 'VALIDATION_ERROR')
        ->assertJsonStructure(['error' => ['errors']]);
});

// --- Unit tests for ApiExceptionHandler ---

beforeEach(function (): void {
    $this->handler = new ApiExceptionHandler();
});

it('handles ValidationException with field errors', function (): void {
    $validator = validator(['email' => ''], ['email' => 'required|email']);
    $e = new ValidationException($validator);

    $response = $this->handler->render($e);

    expect($response->status())->toBe(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->and($response->getData(true)['success'])->toBeFalse()
        ->and($response->getData(true)['error']['code'])->toBe('VALIDATION_ERROR')
        ->and($response->getData(true)['error']['errors'])->toBeArray()->toHaveKey('email');
});

it('handles InvalidCredentialsException as 401', function (): void {
    $response = $this->handler->render(new InvalidCredentialsException());

    expect($response->status())->toBe(Response::HTTP_UNAUTHORIZED)
        ->and($response->getData(true)['success'])->toBeFalse()
        ->and($response->getData(true)['error']['code'])->toBe('INVALID_CREDENTIALS')
        ->and($response->getData(true)['message'])->toBe('The provided credentials are incorrect.');
});

it('handles AuthenticationException', function (): void {
    $response = $this->handler->render(new AuthenticationException());

    expect($response->status())->toBe(Response::HTTP_UNAUTHORIZED)
        ->and($response->getData(true)['error']['code'])->toBe('UNAUTHENTICATED');
});

it('handles ModelNotFoundException as 404', function (): void {
    $response = $this->handler->render(new ModelNotFoundException());

    expect($response->status())->toBe(Response::HTTP_NOT_FOUND)
        ->and($response->getData(true)['error']['code'])->toBe('NOT_FOUND');
});

it('handles NotFoundHttpException as 404', function (): void {
    $response = $this->handler->render(new NotFoundHttpException());

    expect($response->status())->toBe(Response::HTTP_NOT_FOUND)
        ->and($response->getData(true)['error']['code'])->toBe('NOT_FOUND');
});

it('handles AuthorizationException as 403', function (): void {
    $response = $this->handler->render(new AuthorizationException());

    expect($response->status())->toBe(Response::HTTP_FORBIDDEN)
        ->and($response->getData(true)['success'])->toBeFalse()
        ->and($response->getData(true)['error']['code'])->toBe('UNAUTHORIZED')
        ->and($response->getData(true)['message'])->toBe('Unauthorized.');
});

it('handles TooManyRequestsHttpException as 429', function (): void {
    $response = $this->handler->render(new TooManyRequestsHttpException());

    expect($response->status())->toBe(Response::HTTP_TOO_MANY_REQUESTS)
        ->and($response->getData(true)['error']['code'])->toBe('TOO_MANY_REQUESTS');
});

it('handles HttpException with its status code and message', function (): void {
    $e = new HttpException(Response::HTTP_SERVICE_UNAVAILABLE, 'Service down');

    $response = $this->handler->render($e);

    expect($response->status())->toBe(Response::HTTP_SERVICE_UNAVAILABLE)
        ->and($response->getData(true)['success'])->toBeFalse()
        ->and($response->getData(true)['error']['code'])->toBe('HTTP_ERROR')
        ->and($response->getData(true)['message'])->toBe('Service down');
});

it('handles HttpException with empty message uses fallback', function (): void {
    $e = new HttpException(Response::HTTP_SERVICE_UNAVAILABLE);

    $response = $this->handler->render($e);

    expect($response->getData(true)['message'])->toBe('HTTP Error.');
});

it('handles generic Throwable as 500 with hidden message in production', function (): void {
    config(['app.debug' => false]);

    $response = $this->handler->render(new RuntimeException('Sensitive info'));

    expect($response->status())->toBe(Response::HTTP_INTERNAL_SERVER_ERROR)
        ->and($response->getData(true)['error']['code'])->toBe('SERVER_ERROR')
        ->and($response->getData(true)['error']['detail'])->toBe('An unexpected error occurred.');
});

it('exposes exception message in debug mode for generic errors', function (): void {
    config(['app.debug' => true]);

    $response = $this->handler->render(new RuntimeException('Sensitive info'));

    expect($response->getData(true)['error']['detail'])->toBe('Sensitive info');
});
