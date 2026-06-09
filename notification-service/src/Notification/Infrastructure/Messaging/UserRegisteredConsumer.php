<?php

declare(strict_types=1);

namespace App\Notification\Infrastructure\Messaging;

use App\Notification\Application\Handler\SendWelcomeNotificationHandler;
use App\Notification\Infrastructure\Messaging\Message\UserRegisteredMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class UserRegisteredConsumer
{
    public function __construct(
        private readonly SendWelcomeNotificationHandler $handler,
    ) {}

    public function __invoke(UserRegisteredMessage $message): void
    {
        $this->handler->handle($message->userId, $message->email, $message->phoneNumber);
    }
}
