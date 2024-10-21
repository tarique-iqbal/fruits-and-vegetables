<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\Request\VegetableDto;
use App\Mapper\MapperInterface;
use App\Service\VegetableServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class VegetableController extends AbstractApiController
{
    public function __construct(
        private readonly VegetableServiceInterface $vegetableService,
        SerializerInterface $serializer,
        SluggerInterface $asciiSlugger
    ) {
        parent::__construct($serializer, $asciiSlugger);
    }

    protected function getDtoClassName(): string
    {
        return VegetableDto::class;
    }

    #[Route('/vegetables', name: 'vegetable_add', methods: ['POST'])]
    public function postVegetable(
        Request $request,
        ValidatorInterface $validator,
        MapperInterface $vegetableMapper,
    ): JsonResponse {
        $vegetableDto = $this->loadDto($request);

        $violations = $validator->validate($vegetableDto);
        if (count($violations) > 0) {
            throw new ValidationFailedException(null, $violations);
        }

        $vegetable = $vegetableMapper->mapToEntity($vegetableDto);

        $this->vegetableService->addVegetable($vegetable);

        return $this->json($vegetable, Response::HTTP_CREATED);
    }

    #[Route('/vegetables/{page}', name: 'vegetable_list', requirements: ['page' => '\d+'], methods: ['GET'])]
    public function getVegetables(int $page = 1): JsonResponse
    {
        $result = $this->vegetableService->getPaginatedVegetables($page);

        return $this->json($result, Response::HTTP_OK);
    }

    #[Route('/vegetables/{id}', name: 'vegetable_delete', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function deleteVegetable(int $id): JsonResponse
    {
        $vegetable = $this->vegetableService->findById($id);

        if ($vegetable === null) {
            throw new NotFoundHttpException(
                sprintf('Vegetable does not found. id: %s', $id)
            );
        }

        $this->vegetableService->removeVegetable($vegetable);

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
