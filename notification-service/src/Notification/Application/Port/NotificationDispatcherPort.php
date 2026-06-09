<?php

declare(strict_types=1);

namespace App\Notification\Application\Port;

use App\Notification\Application\DTO\NotificationContext;

interface NotificationDispatcherPort
{
    public function dispatch(NotificationContext $context): void;
}
