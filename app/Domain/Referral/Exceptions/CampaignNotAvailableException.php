<?php

namespace App\Domain\Referral\Exceptions;

use Exception;

class CampaignNotAvailableException extends Exception
{
    protected $code = 409;

    public function __construct(string $message = 'Campaign is not available for referral code generation.')
    {
        parent::__construct($message);
    }
}
