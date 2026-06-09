<?php

declare(strict_types=1);

namespace App\User\Application\Query\LoginUser;

use App\User\Application\Port\PasswordHasherPort;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\User\Domain\ValueObject\Email;

final class LoginUserQueryHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly PasswordHasherPort $passwordHasher,
    ) {}

    public function __invoke(LoginUserQuery $query): LoginResult
    {
        $email = new Email($query->email);
        $user = $this->userRepository->findByEmail($email);

        if ($user === null || !$this->passwordHasher->verify($query->plainPassword, $user->getPassword())) {
            throw new \DomainException('Invalid credentials.');
        }

        if (!$user->isActive()) {
            throw new \DomainException('Account is blocked.');
        }

        return new LoginResult($user->getId()->value(), $user->getEmail()->value());
    }
}
