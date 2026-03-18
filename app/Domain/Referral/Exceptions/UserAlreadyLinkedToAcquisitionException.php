<?php

namespace App\Domain\Referral\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class UserAlreadyLinkedToAcquisitionException extends Exception
{
    /**
     * Render the exception as an HTTP response.
     */
    public function render(): JsonResponse
    {
        return response()->json([
            'message' => 'User already linked to a referral acquisition.',
        ], 409);
    }
}
