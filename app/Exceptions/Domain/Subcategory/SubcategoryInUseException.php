<?php

namespace App\Exceptions\Domain\Subcategory;

use App\Exceptions\Domain\DomainException;

class SubcategoryInUseException extends DomainException
{
    public function __construct()
    {
        parent::__construct(
            apiCode: 'SUBCATEGORY_IN_USE',
            status: 409,
            message: 'Cannot delete subcategory. It contains products.'
        );
    }
}