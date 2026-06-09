<?php

declare(strict_types=1);

namespace App\Notification\Application\Port;

use App\Notification\Application\DTO\NotificationContext;
use App\Notification\Domain\Enum\NotificationType;

interface NotificationChannelInterface
{
    public function send(NotificationContext $context): void;

    public function supports(NotificationType $type): bool;
}
