<?php

namespace App\Exceptions;

use Bugsnag;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Validation\ValidationException;
use Logger;
use PDOException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
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
        AuthenticationException::class,
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
     * @param  Exception  $exception
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
     * @param  Request  $request
     * @param  Exception  $exception
     * @return Redirector|RedirectResponse|Response
     *
     * @throws Throwable
     */
    public function render($request, Throwable $exception): Redirector|RedirectResponse|Response
    {
        if ($exception instanceof AuthenticationException) {
//            This we can enable in vue convert
//            if ($request->ajax() || $request->wantsJson()) {
//                return middlewareResponse(trans('lang.unauthorized_please_click_here_to_login_again', ['link' => faveoUrl('auth/login')]), 401);
//            } else {
//                return redirect('login');
//            }
            return parent::render($request, $exception);
        }

        if (stripos($request->url(), 'api') || $request->ajax() || $request->wantsJson()) {
            return $this->responseForApi($request, $exception);
        }

        //if validation exception then let parent class render it
        if ($exception instanceof ValidationException) {
            return parent::render($request, $exception);
        }

        //if model/HTTP not found error show custom 404 irrespective of app debug mode
        if ($exception instanceof NotFoundHttpException) {
            return redirect('404');
        }

        //else render exception based on debug mode
        return $this->renderExceptionBasedOnDebugMode($request, $exception);
    }

    /**
     * Response for exception for APIs.
     *
     * @param  $request
     * @param  Throwable  $exception  Exception instance
     * @return JsonResponse
     */
    protected function responseForApi($request, Throwable $exception): JsonResponse
    {
        switch ($exception) {
            case $exception instanceof MethodNotAllowedHttpException:
                // Handle invalid HTTP method called
                return errorResponse(__('message.method_not_allowed'), 405);

            case $exception instanceof NotFoundHttpException:
                // Handle invalid end point called
                return errorResponse(__('message.invalid-api-endpoint'), 404);

            case $exception instanceof ValidationException:
                return $this->invalidJson($request, $exception);

            case $exception instanceof ThrottleRequestsException:
                return response()->json([
                    'success' => false,
                    'message' => $exception->getMessage(),
                ], $exception->getStatusCode())->withHeaders($exception->getHeaders());

            case $exception instanceof PostTooLargeException:
                //request entity too large error, passing status code 422 as without this, it will not work for the windows on enabling antivirus e.g. Kaspersky
                return errorResponse(__('message.request_entity_too_large_maxsize', ['maxsize' => (int) ini_get('post_max_size')]), 422);

            default:
                // if debug mode is ON, an actual exception message should go else internal-server-error
                return \Config::get('app.debug') ? exceptionResponse($exception) : errorResponse(__('message.internal-server-error'), 500);
        }
    }

    /**
     * Render an exception into an HTTP response based on debug mode.
     *
     * @param  Request  $request
     * @param  Throwable  $exception
     * @return RedirectResponse|Redirector|Response
     *
     * @throws Throwable
     */
    protected function renderExceptionBasedOnDebugMode(Request $request, Throwable $exception): Redirector|RedirectResponse|Response
    {
        //if debug mode enabled or a system is under maintenance mode, redirect to the actual error page else show the custom server error page
        return (config('app.debug') === true) || $exception->getMessage() == 'Service Unavailable' ? parent::render($request, $exception) : response()->view('errors.500', [], 500);
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param  Request  $request
     * @param  AuthenticationException  $exception
     * @return JsonResponse|RedirectResponse
     */
    protected function unauthenticated($request, AuthenticationException $exception): JsonResponse|RedirectResponse
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
     * @param  Throwable  $exception  current Exception instance
     * @return bool false if exception should not be logged in DB, otherwise true
     */
    private function shouldBeLoggedInDB(Throwable $exception): bool
    {
        $notAllowedExceptions = [PDOException::class, NotFoundHttpException::class, AuthenticationException::class, ValidationException::class];
        foreach ($notAllowedExceptions as $notAllowedException) {
            if ($exception instanceof $notAllowedException) {
                return false;
            }
        }

        return true;
    }
}
