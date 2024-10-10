<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\FruitServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class FruitController extends AbstractController
{
    public function __construct(
        private readonly FruitServiceInterface $fruitService,
    ) {
    }

    #[Route('/fruits', name: 'fruit_add', methods: ['POST'])]
    public function postFruit(
        Request $request,
        ValidatorInterface $validator,
    ): JsonResponse {
        $jsonData = $request->getContent();
        $fruit = $this->fruitService->prepareInput($jsonData);

        $violations = $validator->validate($fruit);
        if (count($violations) > 0) {
            throw new ValidationFailedException(null, $violations);
        }

        $this->fruitService->addFruit($fruit);

        return $this->json($fruit, Response::HTTP_CREATED);
    }

    #[Route('/fruits/{page}', name: 'fruit_list', requirements: ['page' => '\d+'], methods: ['GET'])]
    public function getFruits(int $page = 1): JsonResponse
    {
        $result = $this->fruitService->getPaginatedFruits($page);

        return $this->json($result, Response::HTTP_OK);
    }

    #[Route('/fruits/{id}', name: 'fruit_delete', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function deleteFruit(int $id): JsonResponse
    {
        $fruit = $this->fruitService->findById($id);

        if ($fruit === null) {
            throw new NotFoundHttpException(
                sprintf('Fruit does not found. id: %s', $id)
            );
        }

        $this->fruitService->removeFruit($fruit);

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
