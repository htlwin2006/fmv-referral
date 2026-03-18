<?php

namespace App\Domain\Referral\Exceptions;

use Exception;

class ReferralCodeAlreadyExistsException extends Exception
{
    protected $message = 'Referral code already exists.';
    protected $code = 409;
}
