<?php

declare(strict_types=1);

namespace App\Notification\UI\Http\Controller;

use App\Notification\Application\Port\NotificationLogRepositoryPort;
use App\Notification\Domain\Entity\NotificationLog;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/notifications/logs', methods: ['GET'])]
#[OA\Get(
    path: '/notifications/logs',
    operationId: 'getNotificationLogs',
    summary: 'List recent notification delivery logs',
    tags: ['Notifications'],
    parameters: [
        new OA\Parameter(name: 'limit', in: 'query', required: false,
            description: 'Maximum results (1–100, default 20)',
            schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 100, default: 20)
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'List of notification log entries',
            content: new OA\JsonContent(
                type: 'array',
                items: new OA\Items(properties: [
                    new OA\Property(property: 'id', type: 'string', format: 'uuid'),
                    new OA\Property(property: 'userId', type: 'string', format: 'uuid'),
                    new OA\Property(property: 'email', type: 'string', format: 'email'),
                    new OA\Property(property: 'type', type: 'string', example: 'welcome'),
                    new OA\Property(property: 'sentAt', type: 'string', format: 'date-time'),
                ])
            )
        ),
    ]
)]
final class NotificationLogController extends AbstractController
{
    public function __construct(
        private readonly NotificationLogRepositoryPort $logRepository,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $limit = min(100, max(1, (int) ($request->query->get('limit', 20))));

        $logs = $this->logRepository->findRecent($limit);

        return $this->json(
            array_map(fn(NotificationLog $log) => [
                'id'     => $log->getId()->value(),
                'userId' => $log->getUserId(),
                'email'  => $log->getEmail(),
                'type'   => $log->getType()->value,
                'sentAt' => $log->getSentAt()->format(\DateTimeInterface::ATOM),
            ], $logs)
        );
    }
}
