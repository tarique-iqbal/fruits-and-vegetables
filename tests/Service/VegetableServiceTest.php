<?php

namespace App\Tests\Service;

use App\Entity\Vegetable;
use App\Service\VegetableService;
use App\Service\VegetableServiceInterface;
use App\Tests\DataFixtures\VegetableFixtures;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class VegetableServiceTest extends KernelTestCase
{
    private VegetableServiceInterface $vegetableService;

    public function setUp(): void
    {
        $container = static::getContainer();
        $container->get(DatabaseToolCollection::class)
            ->get()
            ->loadFixtures([VegetableFixtures::class]);

        $this->vegetableService = $container->get(VegetableService::class);
    }

    public function testGetPaginatedVegetables(): void
    {
        $result = $this->vegetableService->getPaginatedVegetables(1);

        $this->assertContainsOnlyInstancesOf(Vegetable::class, $result['vegetables']);
        $this->assertInstanceOf(\stdClass::class, $result['pager']);
    }

    public function testGetPaginatedVegetablesInvalidPage(): void
    {
        $this->expectException(BadRequestHttpException::class);

        $this->vegetableService->getPaginatedVegetables(99);
    }
}
