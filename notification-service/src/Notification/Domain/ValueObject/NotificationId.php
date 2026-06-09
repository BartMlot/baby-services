<?php

declare(strict_types=1);

namespace App\Notification\Domain\ValueObject;

use Symfony\Component\Uid\Uuid;

final class NotificationId
{
    private function __construct(private readonly string $value) {}

    public static function generate(): self
    {
        return new self(Uuid::v4()->toRfc4122());
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }
}
