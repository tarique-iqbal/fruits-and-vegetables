<?php

declare(strict_types=1);

namespace App\Service\UnitProcessor;

use App\Entity\Vegetable;
use App\Repository\VegetableRepository;
use Symfony\Component\String\Slugger\SluggerInterface;

readonly class VegetableProcessorService implements UnitProcessorServiceInterface
{
    public function __construct(
        private SluggerInterface $asciiSlugger,
        private VegetableRepository $vegetableRepository,
    ) {
    }

    public function isMatch(string $type): bool
    {
        return ($type === 'vegetable');
    }

    public function process(\stdClass $object): bool
    {
        $alias = $this->asciiSlugger->slug($object->name)
            ->lower()
            ->toString();
        $vegetable = $this->vegetableRepository->findOneBy(['alias' => $alias]);

        if ($vegetable instanceof vegetable) {
            return false;
        }

        $gram = $object->unit === 'kg' ?
            $object->quantity * 1000 :
            $object->quantity;

        $vegetable = new vegetable();
        $vegetable->setName($object->name)
            ->setAlias($alias)
            ->setGram($gram);

        $this->vegetableRepository->add($vegetable);

        return true;
    }
}
