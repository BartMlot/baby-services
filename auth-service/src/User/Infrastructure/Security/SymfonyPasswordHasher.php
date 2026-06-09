<?php

declare(strict_types=1);

namespace App\User\Infrastructure\Security;

use App\User\Application\Port\PasswordHasherPort;
use App\User\Domain\ValueObject\HashedPassword;
use Symfony\Component\PasswordHasher\Hasher\NativePasswordHasher;

final class SymfonyPasswordHasher implements PasswordHasherPort
{
    private readonly NativePasswordHasher $hasher;

    public function __construct()
    {
        $this->hasher = new NativePasswordHasher();
    }

    public function hash(string $plainPassword): string
    {
        return $this->hasher->hash($plainPassword);
    }

    public function verify(string $plainPassword, HashedPassword $hashed): bool
    {
        return $this->hasher->verify($hashed->value(), $plainPassword);
    }
}
