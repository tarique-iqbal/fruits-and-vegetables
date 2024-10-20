<?php

declare(strict_types=1);

namespace App\Component\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class UniqueFruit extends Constraint
{
    public const NOT_UNIQUE_ERROR = '33528a26-afc7-468b-9830-ec13d426a3b2';

    public string $message = 'Fruit is not unique.';

    protected const ERROR_NAMES = [
        self::NOT_UNIQUE_ERROR => 'NOT_UNIQUE_ERROR',
    ];
}