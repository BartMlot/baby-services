<?php

declare(strict_types=1);

namespace App\Notification\UI\Http\Controller;

use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/health', methods: ['GET'])]
#[OA\Get(
    path: '/health',
    operationId: 'healthCheck',
    summary: 'Service health check',
    tags: ['Operations'],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Service is up',
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'status', type: 'string', example: 'ok'),
                new OA\Property(property: 'service', type: 'string', example: 'notification-service'),
            ])
        ),
    ]
)]
final class HealthController extends AbstractController
{
    public function __invoke(): JsonResponse
    {
        return $this->json([
            'status'  => 'ok',
            'service' => 'notification-service',
        ]);
    }
}
