<?php

declare(strict_types=1);

namespace App\Notification\Application\Service;

use App\Notification\Application\DTO\NotificationContext;
use App\Notification\Application\Port\NotificationChannelInterface;
use App\Notification\Application\Port\NotificationDispatcherPort;
use Psr\Log\LoggerInterface;

final class NotificationDispatcher implements NotificationDispatcherPort
{
    /** @param iterable<NotificationChannelInterface> $channels */
    public function __construct(
        private readonly iterable $channels,
        private readonly LoggerInterface $logger,
    ) {}

    public function dispatch(NotificationContext $context): void
    {
        foreach ($this->channels as $channel) {
            if (!$channel->supports($context->type)) {
                continue;
            }

            try {
                $channel->send($context);

                $this->logger->info('Notification sent.', [
                    'channel' => $channel::class,
                    'type'    => $context->type->value,
                    'userId'  => $context->userId,
                ]);
            } catch (\Throwable $e) {
                // Log and continue — best-effort delivery across all channels
                $this->logger->error('Notification channel failed.', [
                    'channel' => $channel::class,
                    'type'    => $context->type->value,
                    'userId'  => $context->userId,
                    'error'   => $e->getMessage(),
                ]);
            }
        }
    }
}
