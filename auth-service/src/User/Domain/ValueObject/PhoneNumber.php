<?php

declare(strict_types=1);

namespace App\User\Domain\ValueObject;

final class PhoneNumber
{
    private readonly string $value;

    public function __construct(string $value)
    {
        $normalized = preg_replace('/\s+/', '', $value);

        if (!preg_match('/^\+[1-9]\d{7,14}$/', $normalized)) {
            throw new \InvalidArgumentException(
                sprintf('"%s" is not a valid E.164 phone number (e.g. +48123456789).', $value)
            );
        }

        $this->value = $normalized;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
