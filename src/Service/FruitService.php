<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Fruit;
use App\Helper\PaginationHelper;
use App\Repository\FruitRepository;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

final readonly class FruitService implements FruitServiceInterface
{
    public function __construct(
        private FruitRepository $fruitRepository,
        private SerializerInterface $serializer,
        private SluggerInterface $asciiSlugger
    ) {
    }

    public function addFruit(Fruit $fruit): void
    {
        $this->fruitRepository->add($fruit);
    }

    public function removeFruit(Fruit $fruit): void
    {
        $this->fruitRepository->remove($fruit);
    }

    public function findById(int $id): Fruit|null
    {
        return $this->fruitRepository->find($id);
    }

    public function prepareInput(string $jsonData): Fruit
    {
        $fruit = $this->serializer->deserialize(
            $jsonData,
            Fruit::class,
            'json'
        );

        $alias = $this->asciiSlugger->slug($fruit->getName())
            ->lower()
            ->toString();
        $fruit->setAlias($alias);

        return $fruit;
    }

    public function getPaginatedFruits(int $page): array
    {
        $query = $this->fruitRepository->getQuery();
        $pager = (new PaginationHelper($query))->paginate($page);
        $fruits = $this->fruitRepository->getPaginatedResult($query, $pager->offset, $pager->limit);

        return [
            'fruits' => $fruits,
            'pager' => $pager
        ];
    }
}
