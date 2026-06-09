<?php

declare(strict_types=1);

namespace App\Notification\Infrastructure\Channel;

use App\Notification\Application\DTO\NotificationContext;
use App\Notification\Application\Port\NotificationChannelInterface;
use App\Notification\Domain\Enum\NotificationType;
use Twilio\Exceptions\TwilioException;
use Twilio\Rest\Client;

/**
 * Delivers SMS notifications via Twilio Programmable Messaging.
 * SMS is only sent when the user provided a phone number at registration.
 */
final class TwilioSmsChannel implements NotificationChannelInterface
{
    public function __construct(
        private readonly Client $twilio,
        private readonly string $fromNumber,
    ) {}

    public function supports(NotificationType $type): bool
    {
        return true;
    }

    public function send(NotificationContext $context): void
    {
        if ($context->phoneNumber === null) {
            return;
        }

        try {
            $this->twilio->messages->create($context->phoneNumber, [
                'from' => $this->fromNumber,
                'body' => $this->message($context),
            ]);
        } catch (TwilioException $e) {
            throw new \RuntimeException(
                sprintf('[Twilio] Failed to send SMS to %s: %s (code: %d)', $context->phoneNumber, $e->getMessage(), $e->getCode()),
                previous: $e,
            );
        }
    }

    private function message(NotificationContext $context): string
    {
        return match ($context->type) {
            NotificationType::WELCOME        => 'Welcome! Your account has been created.',
            NotificationType::PASSWORD_RESET => 'A password reset was requested for your account.',
        };
    }
}
