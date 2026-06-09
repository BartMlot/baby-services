<?php

declare(strict_types=1);

namespace App\User\Infrastructure\Messaging\Message;

use App\User\Domain\Event\UserRegistered;

final class UserRegisteredMessage
{
    public function __construct(
        public readonly string $userId,
        public readonly string $email,
        public readonly string $occurredAt,
        public readonly ?string $phoneNumber = null,
    ) {}

    public static function fromDomainEvent(UserRegistered $event): self
    {
        return new self(
            $event->userId->value(),
            $event->email->value(),
            $event->occurredAt->format(\DateTimeInterface::ATOM),
            $event->phoneNumber?->value(),
        );
    }
}
