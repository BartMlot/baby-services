<?php

declare(strict_types=1);

namespace App\User\Application\Port;

interface EventPublisherPort
{
    public function publish(object $event): void;
}
