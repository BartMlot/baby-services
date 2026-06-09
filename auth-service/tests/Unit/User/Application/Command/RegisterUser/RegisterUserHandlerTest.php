<?php

declare(strict_types=1);

namespace App\Tests\Unit\User\Application\Command\RegisterUser;

use App\User\Application\Command\RegisterUser\RegisterUserCommand;
use App\User\Application\Command\RegisterUser\RegisterUserHandler;
use App\User\Application\Port\EventPublisherPort;
use App\User\Application\Port\PasswordHasherPort;
use App\User\Domain\Entity\User;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\HashedPassword;
use App\User\Domain\ValueObject\UserId;
use PHPUnit\Framework\TestCase;

final class RegisterUserHandlerTest extends TestCase
{
    private UserRepositoryInterface $repository;
    private PasswordHasherPort $passwordHasher;
    private EventPublisherPort $eventPublisher;
    private RegisterUserHandler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(UserRepositoryInterface::class);
        $this->passwordHasher = $this->createMock(PasswordHasherPort::class);
        $this->eventPublisher = $this->createMock(EventPublisherPort::class);

        $this->handler = new RegisterUserHandler(
            $this->repository,
            $this->passwordHasher,
            $this->eventPublisher,
        );
    }

    public function testRegistersNewUserSuccessfully(): void
    {
        $this->repository->method('findByEmail')->willReturn(null);
        $this->passwordHasher->method('hash')->willReturn('$2y$hashed_password');

        $this->repository->expects($this->once())->method('save');
        $this->eventPublisher->expects($this->once())->method('publish');

        ($this->handler)(new RegisterUserCommand('new@example.com', 'password123'));
    }

    public function testThrowsWhenEmailAlreadyTaken(): void
    {
        $existingUser = User::register(
            UserId::generate(),
            new Email('taken@example.com'),
            new HashedPassword('$2y$some_hash'),
        );

        $this->repository->method('findByEmail')->willReturn($existingUser);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('already exists');

        ($this->handler)(new RegisterUserCommand('taken@example.com', 'password123'));
    }

    public function testDoesNotSaveOnInvalidEmail(): void
    {
        $this->repository->expects($this->never())->method('save');

        $this->expectException(\InvalidArgumentException::class);

        ($this->handler)(new RegisterUserCommand('not-an-email', 'password123'));
    }
}
