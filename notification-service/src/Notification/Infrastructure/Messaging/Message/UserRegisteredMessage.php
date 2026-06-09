<?php

declare(strict_types=1);

namespace App\Notification\Infrastructure\Messaging\Message;

/**
 * Local representation of the auth.events/user.registered contract.
 * Intentionally mirrors the auth-service payload — no shared kernel.
 */
final class UserRegisteredMessage
{
    public function __construct(
        public readonly string $userId,
        public readonly string $email,
        public readonly string $occurredAt,
        public readonly ?string $phoneNumber = null,
    ) {}
}
