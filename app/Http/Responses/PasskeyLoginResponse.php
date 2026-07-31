<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Laravel\Passkeys\Contracts\PasskeyLoginResponse as PasskeyLoginResponseContract;

class PasskeyLoginResponse implements PasskeyLoginResponseContract
{
    public function toResponse($request): JsonResponse
    {
        return response()->json([
            'redirect' => redirect()->intended(route('dashboard'))->getTargetUrl(),
        ]);
    }
}
