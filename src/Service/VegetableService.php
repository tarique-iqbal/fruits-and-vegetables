<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Vegetable;
use App\Helper\PaginationHelper;
use App\Repository\VegetableRepository;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

final readonly class VegetableService implements VegetableServiceInterface
{
    public function __construct(
        private VegetableRepository $vegetableRepository,
        private SerializerInterface $serializer,
        private SluggerInterface $asciiSlugger
    ) {
    }

    public function addVegetable(Vegetable $vegetable): void
    {
        $this->vegetableRepository->add($vegetable);
    }

    public function removeVegetable(Vegetable $vegetable): void
    {
        $this->vegetableRepository->remove($vegetable);
    }

    public function findById(int $id): Vegetable|null
    {
        return $this->vegetableRepository->find($id);
    }

    public function prepareInput(string $jsonData): Vegetable
    {
        $vegetable = $this->serializer->deserialize(
            $jsonData,
            Vegetable::class,
            'json'
        );

        $alias = $this->asciiSlugger->slug($vegetable->getName())
            ->lower()
            ->toString();
        $vegetable->setAlias($alias);

        return $vegetable;
    }

    public function getPaginatedVegetables(int $page): array
    {
        $query = $this->vegetableRepository->getQuery();
        $pager = (new PaginationHelper($query))->paginate($page);
        $vegetables = $this->vegetableRepository->getPaginatedResult($query, $pager->offset, $pager->limit);

        return [
            'vegetables' => $vegetables,
            'pager' => $pager
        ];
    }
}
