<?php

namespace App\Exceptions\Domain\Category;

use App\Exceptions\Domain\DomainException;

class CategoryInUseException extends DomainException
{
    public function __construct()
    {
        parent::__construct(
            apiCode: 'CATEGORY_IN_USE',
            status: 409,
            message: 'Cannot delete category. It contains subcategories with products.'
        );
    }
}