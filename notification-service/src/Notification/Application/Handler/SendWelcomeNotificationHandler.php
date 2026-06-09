<?php

declare(strict_types=1);

namespace App\Notification\Application\Handler;

use App\Notification\Application\DTO\NotificationContext;
use App\Notification\Application\Port\NotificationLogRepositoryPort;
use App\Notification\Application\Service\NotificationDispatcher;
use App\Notification\Domain\Entity\NotificationLog;
use App\Notification\Domain\Enum\NotificationType;
use App\Notification\Domain\ValueObject\NotificationId;

final class SendWelcomeNotificationHandler
{
    public function __construct(
        private readonly NotificationDispatcher $dispatcher,
        private readonly NotificationLogRepositoryPort $logRepository,
    ) {}

    public function handle(string $userId, string $email, ?string $phoneNumber): void
    {
        $context = new NotificationContext(
            $userId,
            $email,
            $phoneNumber,
            NotificationType::WELCOME,
        );

        $this->dispatcher->dispatch($context);

        $this->logRepository->save(
            NotificationLog::create(
                NotificationId::generate(),
                $userId,
                $email,
                NotificationType::WELCOME,
            )
        );
    }
}
