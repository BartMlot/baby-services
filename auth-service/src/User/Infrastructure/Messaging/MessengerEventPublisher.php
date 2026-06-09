<?php

declare(strict_types=1);

namespace App\User\Infrastructure\Messaging;

use App\User\Application\Port\EventPublisherPort;
use App\User\Domain\Event\UserRegistered;
use App\User\Infrastructure\Messaging\Message\UserRegisteredMessage;
use Symfony\Component\Messenger\MessageBusInterface;

final class MessengerEventPublisher implements EventPublisherPort
{
    public function __construct(
        private readonly MessageBusInterface $bus,
    ) {}

    public function publish(object $event): void
    {
        $message = match (true) {
            $event instanceof UserRegistered => UserRegisteredMessage::fromDomainEvent($event),
            default => throw new \InvalidArgumentException(
                sprintf('No message mapping defined for event: %s', $event::class)
            ),
        };

        $this->bus->dispatch($message);
    }
}
