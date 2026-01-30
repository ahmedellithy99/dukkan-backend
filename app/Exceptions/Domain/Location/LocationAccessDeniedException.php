<?php

namespace App\Exceptions\Domain\Location;

use App\Exceptions\Domain\DomainException;

class LocationAccessDeniedException extends DomainException
{
    public function __construct(string $message = 'Access denied to location.')
    {
        parent::__construct('LOCATION_ACCESS_DENIED', 403, $message);
    }
}