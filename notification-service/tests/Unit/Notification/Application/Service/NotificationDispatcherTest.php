<?php

declare(strict_types=1);

namespace App\Tests\Unit\Notification\Application\Service;

use App\Notification\Application\DTO\NotificationContext;
use App\Notification\Application\Port\NotificationChannelInterface;
use App\Notification\Application\Service\NotificationDispatcher;
use App\Notification\Domain\Enum\NotificationType;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

final class NotificationDispatcherTest extends TestCase
{
    private function makeContext(): NotificationContext
    {
        return new NotificationContext('user-id', 'user@example.com', null, NotificationType::WELCOME);
    }

    public function testCallsAllSupportingChannels(): void
    {
        $channelA = $this->createMock(NotificationChannelInterface::class);
        $channelB = $this->createMock(NotificationChannelInterface::class);

        $channelA->method('supports')->willReturn(true);
        $channelB->method('supports')->willReturn(true);

        $channelA->expects($this->once())->method('send');
        $channelB->expects($this->once())->method('send');

        $dispatcher = new NotificationDispatcher([$channelA, $channelB], new NullLogger());
        $dispatcher->dispatch($this->makeContext());
    }

    public function testSkipsChannelsThatDontSupportType(): void
    {
        $channel = $this->createMock(NotificationChannelInterface::class);
        $channel->method('supports')->willReturn(false);
        $channel->expects($this->never())->method('send');

        $dispatcher = new NotificationDispatcher([$channel], new NullLogger());
        $dispatcher->dispatch($this->makeContext());
    }

    public function testContinuesToNextChannelWhenOneFails(): void
    {
        $failing  = $this->createMock(NotificationChannelInterface::class);
        $fallback = $this->createMock(NotificationChannelInterface::class);

        $failing->method('supports')->willReturn(true);
        $failing->method('send')->willThrowException(new \RuntimeException('Channel error'));

        $fallback->method('supports')->willReturn(true);
        $fallback->expects($this->once())->method('send');

        $dispatcher = new NotificationDispatcher([$failing, $fallback], new NullLogger());
        $dispatcher->dispatch($this->makeContext());
    }
}
