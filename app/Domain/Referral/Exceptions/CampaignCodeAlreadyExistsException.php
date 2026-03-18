<?php

namespace App\Domain\Referral\Exceptions;

use Exception;

class CampaignCodeAlreadyExistsException extends Exception
{
    protected $message = 'Campaign code already exists.';
    protected $code = 409;
}
