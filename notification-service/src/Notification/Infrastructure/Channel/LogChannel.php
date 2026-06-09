<?php

declare(strict_types=1);

namespace App\Notification\Infrastructure\Channel;

use App\Notification\Application\DTO\NotificationContext;
use App\Notification\Application\Port\NotificationChannelInterface;
use App\Notification\Domain\Enum\NotificationType;
use Psr\Log\LoggerInterface;

final class LogChannel implements NotificationChannelInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {}

    public function supports(NotificationType $type): bool
    {
        return true;
    }

    public function send(NotificationContext $context): void
    {
        $this->logger->info('[LogChannel] Notification dispatched.', [
            'type'    => $context->type->value,
            'userId'  => $context->userId,
            'email'   => $context->email,
            'phone'   => $context->phoneNumber,
        ]);
    }
}
