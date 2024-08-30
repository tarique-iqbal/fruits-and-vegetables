<?php

declare(strict_types=1);

namespace App\Service\UnitProcessor;

interface UnitProcessorServiceInterface
{
    public function isMatch(string $type): bool;

    public function process(\stdClass $object): bool;
}