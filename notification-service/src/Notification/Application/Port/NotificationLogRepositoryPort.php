<?php

declare(strict_types=1);

namespace App\Notification\Application\Port;

use App\Notification\Domain\Entity\NotificationLog;

interface NotificationLogRepositoryPort
{
    public function save(NotificationLog $log): void;

    /** @return NotificationLog[] */
    public function findRecent(int $limit): array;
}
