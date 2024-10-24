<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\Request\FruitDto;
use App\Mapper\MapperInterface;
use App\Service\FruitServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Webmozart\Assert\Assert;

final class FruitController extends AbstractApiController
{
    public function __construct(
        private readonly FruitServiceInterface $fruitService,
        SerializerInterface $serializer,
        SluggerInterface $asciiSlugger,
    ) {
        parent::__construct($serializer, $asciiSlugger);
    }

    protected function getDtoClassName(): string
    {
        return FruitDto::class;
    }

    #[Route('/fruits', name: 'fruit_add', methods: ['POST'])]
    public function postFruit(
        Request $request,
        ValidatorInterface $validator,
        MapperInterface $fruitMapper,
    ): JsonResponse {
        $fruitDto = $this->loadDto($request);

        $violations = $validator->validate($fruitDto);
        if (count($violations) > 0) {
            throw new ValidationFailedException(null, $violations);
        }

        $fruit = $fruitMapper->mapToEntity($fruitDto);

        $this->fruitService->addFruit($fruit);

        return $this->json($fruit, Response::HTTP_CREATED);
    }

    #[Route('/fruits', name: 'fruit_list', methods: ['GET'])]
    public function getFruits(
        Request $request,
        MapperInterface $fruitMapper,
    ): JsonResponse {
        $page = $request->query->get('page', 1);
        $unit = $request->query->get('unit', self::DEFAULT_UNIT);

        Assert::numeric($page, sprintf('Page expected to be numeric. Received: %s', $page));
        Assert::oneOf($unit, self::UNIT_LIST, sprintf('Unit must be one of %s', implode(', ', self::UNIT_LIST)));

        $result = $this->fruitService->getPaginatedFruits((int) $page);
        $result['fruits'] = $fruitMapper->mapAllToDto($result['fruits']);

        return $this->json(
            data: $result,
            status: Response::HTTP_OK,
            context: ['groups' => [$unit, 'list']]
        );
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
