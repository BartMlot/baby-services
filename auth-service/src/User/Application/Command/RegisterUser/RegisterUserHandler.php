<?php

declare(strict_types=1);

namespace App\User\Application\Command\RegisterUser;

use App\User\Application\Port\EventPublisherPort;
use App\User\Application\Port\PasswordHasherPort;
use App\User\Domain\Entity\User;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\HashedPassword;
use App\User\Domain\ValueObject\PhoneNumber;
use App\User\Domain\ValueObject\UserId;

final class RegisterUserHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly PasswordHasherPort $passwordHasher,
        private readonly EventPublisherPort $eventPublisher,
    ) {}

    public function __invoke(RegisterUserCommand $command): void
    {
        $email = new Email($command->email);

        if ($this->userRepository->findByEmail($email) !== null) {
            throw new \DomainException('A user with this email address already exists.');
        }

        $hashedPassword = new HashedPassword(
            $this->passwordHasher->hash($command->plainPassword)
        );

        $phoneNumber = $command->phoneNumber !== null
            ? new PhoneNumber($command->phoneNumber)
            : null;

        $user = User::register(
            UserId::generate(),
            $email,
            $hashedPassword,
            $phoneNumber,
        );

        $this->userRepository->save($user);

        foreach ($user->pullDomainEvents() as $event) {
            $this->eventPublisher->publish($event);
        }
    }
}
