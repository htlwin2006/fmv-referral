<?php

namespace App\Domain\Referral\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class ReferralCodeNotAvailableException extends Exception
{
    /**
     * Render the exception as an HTTP response.
     */
    public function render(): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage() ?: 'Referral code is not available.',
        ], 409);
    }
}
