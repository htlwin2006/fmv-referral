<?php

namespace App\Domain\Referral\Exceptions;

use Exception;

class CampaignNotFoundException extends Exception
{
    protected $message = 'Campaign not found.';
    protected $code = 404;
}
