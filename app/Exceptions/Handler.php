<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
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
            Log::channel('daily')->error($exception);

            if (config('app.sentry_reporting') && ! app()->environment('production')) {
                Integration::captureUnhandledException($exception);
            }

            if (isInstall()) {
                Logger::exception($exception);
            }
        }

        parent::report($exception);
    }

    #[Override]
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        return redirect()->guest(route('login'));
    }
}
