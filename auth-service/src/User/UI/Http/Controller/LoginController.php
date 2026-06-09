<?php

declare(strict_types=1);

namespace App\User\UI\Http\Controller;

use App\User\Application\Query\LoginUser\LoginResult;
use App\User\Application\Query\LoginUser\LoginUserQuery;
use App\User\Infrastructure\Security\JwtUserAdapter;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/login', methods: ['POST'])]
#[OA\Post(
    path: '/login',
    operationId: 'loginUser',
    summary: 'Authenticate and receive a JWT token',
    tags: ['Auth'],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['email', 'password'],
            properties: [
                new OA\Property(property: 'email', type: 'string', format: 'email', example: 'user@example.com'),
                new OA\Property(property: 'password', type: 'string', example: 'secret123'),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Authentication successful',
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'token', type: 'string', example: 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...'),
                new OA\Property(property: 'user', type: 'object', properties: [
                    new OA\Property(property: 'id', type: 'string', format: 'uuid'),
                    new OA\Property(property: 'email', type: 'string', format: 'email'),
                ]),
            ])
        ),
        new OA\Response(response: 400, description: 'Missing required fields'),
        new OA\Response(response: 401, description: 'Invalid credentials'),
    ]
)]
final class LoginController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $queryBus,
        private readonly JWTTokenManagerInterface $jwtManager,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        if (!$email || !$password) {
            return $this->json(['error' => 'Fields "email" and "password" are required.'], 400);
        }

        try {
            $envelope = $this->queryBus->dispatch(new LoginUserQuery($email, $password));
            /** @var LoginResult $result */
            $result = $envelope->last(HandledStamp::class)->getResult();
        } catch (HandlerFailedException $e) {
            $cause = $e->getPrevious();
            if ($cause instanceof \DomainException) {
                return $this->json(['error' => $cause->getMessage()], 401);
            }
            if ($cause instanceof \InvalidArgumentException) {
                return $this->json(['error' => $cause->getMessage()], 422);
            }
            throw $e;
        }

        $token = $this->jwtManager->create(new JwtUserAdapter($result->email, $result->userId));

        return $this->json([
            'token' => $token,
            'user'  => [
                'id'    => $result->userId,
                'email' => $result->email,
            ],
        ]);
    }
}
