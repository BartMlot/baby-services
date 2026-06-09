<?php

declare(strict_types=1);

namespace App\User\Application\Port;

use App\User\Domain\ValueObject\HashedPassword;

interface PasswordHasherPort
{
    public function hash(string $plainPassword): string;

    public function verify(string $plainPassword, HashedPassword $hashed): bool;
}
