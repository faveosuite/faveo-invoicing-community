<?php

namespace App\Exceptions;

use App\Enums\FaveoStatusCode;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Log;
use Logger;
use Override;
use PDOException;
use Sentry\Laravel\Integration;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * Exceptions that should not be reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        AuthenticationException::class,
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        TokenMismatchException::class,
        ValidationException::class,
        PDOException::class,
        NotFoundHttpException::class,
        MethodNotAllowedHttpException::class,
    ];

    #[Override]
    public function report(Throwable $exception): void
    {
        if ($this->shouldReport($exception)) {
            $this->logException($exception);
            $this->reportToSentry($exception);
            $this->reportToApplicationLogger($exception);
        }

        parent::report($exception);
    }

    #[Override]
    public function render($request, Throwable $exception)
    {
        if ($this->isApiRequest($request)) {
            return $this->responseForApi($request, $exception);
        }

        return parent::render($request, $exception);
    }

    /**
     * Determine whether the request expects an API response.
     */
    private function isApiRequest(Request $request): bool
    {
        return $request->is('api/*')
            || $request->ajax()
            || $request->wantsJson();
    }

    /**
     * Log the exception to the application log.
     */
    private function logException(Throwable $exception): void
    {
        Log::channel('daily')->error($exception);
    }

    /**
     * Report the exception to Sentry when enabled.
     */
    private function reportToSentry(Throwable $exception): void
    {
        if (config('app.sentry_reporting') && app()->environment('production')) {
            Integration::captureUnhandledException($exception);
        }
    }

    /**
     * Report the exception to the application's logger.
     */
    private function reportToApplicationLogger(Throwable $exception): void
    {
        if (isInstall()) {
            Logger::exception($exception);
        }
    }

    /**
     * Build an API response for the given exception.
     */
    protected function responseForApi(Request $request, Throwable $exception): JsonResponse {
        return match (true) {
            $exception instanceof AuthenticationException => $this->unauthenticatedResponse(),

            $exception instanceof MethodNotAllowedHttpException => $this->methodNotAllowedResponse(),

            $exception instanceof ModelNotFoundException => $this->notFoundResponse(),

            $exception instanceof NotFoundHttpException => $this->invalidEndpointResponse(),

            $exception instanceof ValidationException => $this->invalidJson($request, $exception),

            $exception instanceof ThrottleRequestsException => $this->throttleResponse($exception),

            $exception instanceof PostTooLargeException => $this->requestTooLargeResponse(),

            // Covers abort($code, $message) calls (e.g. abort_if(..., 403, 'Forbidden')) not
            // already special-cased above — preserves the exception's own status and message.
            $exception instanceof HttpException => $this->httpExceptionResponse($exception),

            default => $this->exceptionResponse($exception),
        };
    }

    /**
     * Response for an unauthenticated API request. Mirrors unauthenticated()'s JSON
     * branch directly (rather than calling it) since that method can also return a
     * RedirectResponse, which would violate this method's JsonResponse return type.
     */
    private function unauthenticatedResponse(): JsonResponse
    {
        return response()->json(['error' => 'Unauthenticated.'], 401);
    }

    /**
     * Response for a generic HTTP exception (e.g. abort($code, $message)) not already
     * special-cased above — preserves the exception's own status code and message.
     */
    private function httpExceptionResponse(HttpException $exception): JsonResponse
    {
        return errorResponse($exception->getMessage() ?: 'Forbidden', $exception->getStatusCode());
    }

    /**
     * Response for an invalid HTTP method.
     */
    private function methodNotAllowedResponse(): JsonResponse
    {
        return errorResponse(__('lang.method_not_allowed'), FaveoStatusCode::InvalidMethod->value);
    }

    /**
     * Response for a missing record.
     */
    private function notFoundResponse(): JsonResponse
    {
        return errorResponse(__('lang.record_not_found'), FaveoStatusCode::NotFound->value);
    }

    /**
     * Response for an invalid API endpoint.
     */
    private function invalidEndpointResponse(): JsonResponse
    {
        return errorResponse(__('lang.invalid-api-endpoint'), FaveoStatusCode::NotFound->value);
    }

    /**
     * Response when API rate limit is exceeded.
     */
    private function throttleResponse(ThrottleRequestsException $exception): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $exception->getMessage(),
        ], $exception->getStatusCode())->withHeaders($exception->getHeaders());
    }

    /**
     * Response when the request exceeds PHP's POST limit.
     */
    private function requestTooLargeResponse(): JsonResponse
    {
        return errorResponse(__('lang.request_entity_too_large_maxsize', ['maxsize' => (int) ini_get('post_max_size'),]), 422);
    }

    /**
     * Response for an unhandled exception.
     */
    private function exceptionResponse(Throwable $exception): JsonResponse
    {
        return config('app.debug')
            ? exceptionResponse($exception)
            : errorResponse(__('lang.internal-server-error'), FaveoStatusCode::Exception->value);
    }

    /**
     * Response for a failed FormRequest/$request->validate() call on a JSON request.
     * Mirrors RequestJsonValidation::failedValidation() so every validation failure —
     * whether the Request class uses that trait or not — gets the same shape.
     */
    #[Override]
    protected function invalidJson($request, ValidationException $exception)
    {
        $formattedErrors = [];
        foreach ($exception->errors() as $key => $messages) {
            $formattedErrors[$key] = $messages[0];
        }

        return errorResponse($formattedErrors, FaveoStatusCode::ValidationError->value);
    }

    #[Override]
    protected function unauthenticated($request, AuthenticationException $exception) {

        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        return redirect()->guest(route('login'));
    }
}
