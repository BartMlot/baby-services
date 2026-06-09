<?php

declare(strict_types=1);

namespace App\Tests\Unit\Notification\Application\Handler;

use App\Notification\Application\DTO\NotificationContext;
use App\Notification\Application\Handler\SendWelcomeNotificationHandler;
use App\Notification\Application\Port\NotificationLogRepositoryPort;
use App\Notification\Application\Service\NotificationDispatcher;
use App\Notification\Domain\Entity\NotificationLog;
use App\Notification\Domain\Enum\NotificationType;
use PHPUnit\Framework\TestCase;

final class SendWelcomeNotificationHandlerTest extends TestCase
{
    private NotificationDispatcher $dispatcher;
    private NotificationLogRepositoryPort $logRepository;
    private SendWelcomeNotificationHandler $handler;

    protected function setUp(): void
    {
        $this->dispatcher = $this->createMock(NotificationDispatcher::class);
        $this->logRepository = $this->createMock(NotificationLogRepositoryPort::class);
        $this->handler = new SendWelcomeNotificationHandler($this->dispatcher, $this->logRepository);
    }

    public function testDispatchesNotificationAndPersistsLog(): void
    {
        $userId = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890';
        $email = 'user@example.com';
        $phone = '+48123456789';

        $this->dispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function (NotificationContext $ctx) use ($userId, $email, $phone): bool {
                return $ctx->userId === $userId
                    && $ctx->email === $email
                    && $ctx->phoneNumber === $phone
                    && $ctx->type === NotificationType::WELCOME;
            }));

        $this->logRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (NotificationLog $log) use ($userId, $email): bool {
                return $log->getUserId() === $userId
                    && $log->getEmail() === $email
                    && $log->getType() === NotificationType::WELCOME;
            }));

        $this->handler->handle($userId, $email, $phone);
    }

    public function testWorksWithoutPhoneNumber(): void
    {
        $this->dispatcher->expects($this->once())->method('dispatch')
            ->with($this->callback(fn(NotificationContext $ctx) => $ctx->phoneNumber === null));

        $this->logRepository->expects($this->once())->method('save');

        $this->handler->handle('some-id', 'user@example.com', null);
    }

    public function testDispatcherFailurePropagatesException(): void
    {
        $this->dispatcher->method('dispatch')
            ->willThrowException(new \RuntimeException('Channel error'));

        $this->logRepository->expects($this->never())->method('save');

        $this->expectException(\RuntimeException::class);

        $this->handler->handle('some-id', 'user@example.com', null);
    }
}
