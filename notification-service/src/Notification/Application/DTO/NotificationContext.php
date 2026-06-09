<?php

declare(strict_types=1);

namespace App\Notification\Application\DTO;

use App\Notification\Domain\Enum\NotificationType;

final class NotificationContext
{
    public function __construct(
        public readonly string $userId,
        public readonly string $email,
        public readonly ?string $phoneNumber,
        public readonly NotificationType $type,
    ) {}
}
