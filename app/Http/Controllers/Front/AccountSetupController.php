<?php

declare(strict_types=1);

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Controller;
use App\Model\Common\State;
use App\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * One-time billing details step after login (/account-setup) for clients who
 * signed up without them. See User::needsAccountSetup() and LoginController::redirectPath().
 */
class AccountSetupController extends Controller
{
    public function show(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        return successResponse('', [
            'company' => (string) $user->company,
            'address' => (string) $user->address,
            'town' => (string) $user->town,
            'zip' => (string) $user->zip,
            // Prefilled from the visitor's location at sign-up; the user confirms it here.
            'country' => (string) $user->country,
            'state' => (string) $user->state,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'company' => ['required', 'string', 'max:50'],
            'address' => ['required', 'string', 'max:255', 'regex:/^[^<>]*$/'],
            'town' => ['nullable', 'string', 'max:100'],
            'zip' => ['nullable', 'string', 'max:20'],
            'country' => ['required', 'exists:countries,country_code_char2'],
            'state' => [Rule::requiredIf(fn (): bool => State::where('country_code', $request->input('country'))->exists())],
        ]);

        try {
            /** @var User $user */
            $user = Auth::user();
            $user->fill([
                'company' => strip_tags((string) $request->input('company')),
                'address' => strip_tags((string) $request->input('address')),
                'town' => strip_tags((string) $request->input('town', '')),
                'zip' => strip_tags((string) $request->input('zip', '')),
                'country' => $request->input('country'),
                'state' => $request->input('state'),
            ])->save();

            // Same destination logic as login: the page they were heading to before setup.
            return successResponse('', ['redirect' => (string) (new LoginController)->redirectPath()]); // @phpstan-ignore cast.string
        } catch (Exception $exception) {
            \Logger::exception($exception);

            return errorResponse(__('message.sorry_something_wrong'));
        }
    }
}
