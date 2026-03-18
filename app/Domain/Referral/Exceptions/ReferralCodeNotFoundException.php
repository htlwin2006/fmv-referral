<?php

namespace App\Domain\Referral\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class ReferralCodeNotFoundException extends Exception
{
    /**
     * Render the exception as an HTTP response.
     */
    public function render(): JsonResponse
    {
        return response()->json([
            'message' => 'Referral code not found.',
        ], 404);
    }
}
