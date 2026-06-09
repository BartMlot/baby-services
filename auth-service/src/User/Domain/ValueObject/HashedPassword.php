<?php

declare(strict_types=1);

namespace App\User\Domain\ValueObject;

final class HashedPassword
{
    private readonly string $value;

    public function __construct(string $value)
    {
        if (empty(trim($value))) {
            throw new \InvalidArgumentException('Hashed password cannot be empty.');
        }

        $this->value = $value;
    }

    public function value(): string
    {
        return $this->value;
    }
}
