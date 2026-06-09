<?php

declare(strict_types=1);

namespace App\User\UI\Http\Controller;

use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/me', methods: ['GET'])]
#[OA\Get(
    path: '/me',
    operationId: 'getCurrentUser',
    summary: 'Get current authenticated user identity',
    tags: ['Auth'],
    security: [['bearerAuth' => []]],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Current user data decoded from JWT',
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'email', type: 'string', format: 'email'),
                new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'string'), example: ['ROLE_USER']),
            ])
        ),
        new OA\Response(response: 401, description: 'Missing or invalid JWT token'),
    ]
)]
final class MeController extends AbstractController
{
    public function __invoke(): JsonResponse
    {
        /** @var \Lexik\Bundle\JWTAuthenticationBundle\Security\User\JWTUser $user */
        $user = $this->getUser();

        return $this->json([
            'email' => $user->getUserIdentifier(),
            'roles' => $user->getRoles(),
        ]);
    }
}
