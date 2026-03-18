<?php

namespace App\Domain\Referral\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class ReferralAttributionNotFoundException extends Exception
{
    /**
     * Render the exception as an HTTP response.
     */
    public function render(): JsonResponse
    {
        return response()->json([
            'message' => 'Referral attribution not found.',
        ], 404);
    }
}
