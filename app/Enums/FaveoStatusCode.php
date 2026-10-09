<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * App-wide status codes for JSON API responses (errorResponse()/successResponse()).
 * Add new cases here instead of introducing new bare constants or config keys.
 */
enum FaveoStatusCode: int
{
    case Success = 200;
    case Warning = 211;
    case Info = 212;
    case Redirect = 301;
    case TempRedirect = 302;
    case Error = 400;
    case Unauthorized = 401;
    case AccessDenied = 403;
    case NotFound = 404;
    case InvalidMethod = 405;
    case InvalidUrl = 410;
    case ValidationError = 412;
    case UnprocessableEntity = 422;
    case TooManyAttempts = 429;
    case Exception = 500;
}
