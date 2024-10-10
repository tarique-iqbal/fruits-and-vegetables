<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Vegetable;

interface VegetableServiceInterface
{
    public function addVegetable(Vegetable $vegetable): void;

    public function removeVegetable(Vegetable $vegetable): void;

    public function findById(int $id): Vegetable|null;

    public function prepareInput(string $jsonData): Vegetable;

    public function getPaginatedVegetables(int $page): array;
}
