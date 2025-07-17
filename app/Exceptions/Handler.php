<?php

namespace App\Exceptions;

use Bugsnag;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Logger;
use PDOException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Auth\Access\AuthorizationException::class,
        \Symfony\Component\HttpKernel\Exception\HttpException::class,
        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        \Illuminate\Session\TokenMismatchException::class,
        \Illuminate\Validation\ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Throwable $exception)
    {
        // Check if the exception is an UnauthenticatedException
        if (! $exception instanceof AuthenticationException) {
            // Send unhandled exceptions to Bugsnag
            $this->reportToBugsnag($exception);

            // Log the exception
            \Log::channel('daily')->error($exception);
        }

        parent::report($exception);
        // Log exception in database if not PDO exception
        if ($this->shouldBeLoggedInDB($exception) && isInstall()) {
            // Log exception to database
            Logger::exception($exception);
        }
    }

    /**
     * Report to Bugsnag.
     *
     * @param  Exception  $exception  Exception instance
     * @return void
     */
    protected function reportToBugsnag(Throwable $exception)
    {
        // Check bugsnag reporting is active
        if (config('app.bugsnag_reporting')) {
            Bugsnag::notifyException($exception);
        }
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Throwable $exception)
    {
        return parent::render($request, $exception);
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Illuminate\Http\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        return redirect()->guest(route('login'));
    }

    /**
     * Function to check the exception should be stored in database exception logs
     * or not.
     *
     * @param  \Throwable  $exception  current Exception instance
     * @return bool false if exception should not be logged in DB, otherwise true
     */
    private function shouldBeLoggedInDB(Throwable $exception)
    {
        $notAllowedExceptions = [PDOException::class, NotFoundHttpException::class, AuthenticationException::class];
        foreach ($notAllowedExceptions as $notAllowedException) {
            if ($exception instanceof $notAllowedException) {
                return false;
            }
        }

        return true;
    }
}
