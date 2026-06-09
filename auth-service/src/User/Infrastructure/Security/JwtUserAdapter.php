<?php

declare(strict_types=1);

namespace App\User\Infrastructure\Security;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Minimal UserInterface adapter for JWT token generation.
 * Does not represent a persisted user — used only by JWTTokenManagerInterface::create().
 */
final class JwtUserAdapter implements UserInterface
{
    public function __construct(
        private readonly string $email,
        private readonly string $userId,
    ) {}

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    public function eraseCredentials(): void {}

    public function getUserId(): string
    {
        return $this->userId;
    }
}
