<?php

declare(strict_types=1);

namespace App\User\UI\Http\Controller;

use App\User\Application\Command\RegisterUser\RegisterUserCommand;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/register', methods: ['POST'])]
#[OA\Post(
    path: '/register',
    operationId: 'registerUser',
    summary: 'Register a new user',
    tags: ['Auth'],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['email', 'password'],
            properties: [
                new OA\Property(property: 'email', type: 'string', format: 'email', example: 'user@example.com'),
                new OA\Property(property: 'password', type: 'string', minLength: 8, example: 'secret123'),
                new OA\Property(property: 'phone', type: 'string', nullable: true, example: '+48123456789',
                    description: 'Optional. E.164 format. Required to receive SMS notifications.'),
            ]
        )
    ),
    responses: [
        new OA\Response(response: 201, description: 'User registered successfully',
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'message', type: 'string', example: 'User registered successfully.'),
            ])
        ),
        new OA\Response(response: 400, description: 'Missing required fields'),
        new OA\Response(response: 409, description: 'Email already taken'),
        new OA\Response(response: 422, description: 'Validation error (invalid email or phone format)'),
    ]
)]
final class RegisterController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $commandBus,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;
        $phoneNumber = $data['phone'] ?? null;

        if (!$email || !$password) {
            return $this->json(['error' => 'Fields "email" and "password" are required.'], 400);
        }

        if (strlen($password) < 8) {
            return $this->json(['error' => 'Password must be at least 8 characters.'], 422);
        }

        try {
            $this->commandBus->dispatch(new RegisterUserCommand($email, $password, $phoneNumber));
        } catch (HandlerFailedException $e) {
            // Messenger wraps handler exceptions — unwrap to get the original domain exception.
            $cause = $e->getPrevious();
            if ($cause instanceof \DomainException) {
                return $this->json(['error' => $cause->getMessage()], 409);
            }
            if ($cause instanceof \InvalidArgumentException) {
                return $this->json(['error' => $cause->getMessage()], 422);
            }
            throw $e;
        }

        return $this->json(['message' => 'User registered successfully.'], 201);
    }
}
