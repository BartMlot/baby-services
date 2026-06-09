<?php

declare(strict_types=1);

namespace App\User\Application\Query\LoginUser;

final class LoginResult
{
    public function __construct(
        public readonly string $userId,
        public readonly string $email,
    ) {}
}
