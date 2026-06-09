<?php

declare(strict_types=1);

namespace App\Notification\Infrastructure\Channel;

use App\Notification\Application\DTO\NotificationContext;
use App\Notification\Application\Port\NotificationChannelInterface;
use App\Notification\Domain\Enum\NotificationType;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

/**
 * Delivers email notifications via Mailtrap Email Sending API.
 */
final class MailtrapEmailChannel implements NotificationChannelInterface
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly string $from,
    ) {}

    public function supports(NotificationType $type): bool
    {
        return true;
    }

    public function send(NotificationContext $context): void
    {
        $email = (new Email())
            ->from($this->from)
            ->to($context->email)
            ->subject($this->subject($context->type))
            ->text($this->body($context));

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            throw new \RuntimeException(
                sprintf('[Mailtrap] Failed to send "%s" to %s: %s', $context->type->value, $context->email, $e->getMessage()),
                previous: $e,
            );
        }
    }

    private function subject(NotificationType $type): string
    {
        return match ($type) {
            NotificationType::WELCOME        => 'Welcome! Your account is ready.',
            NotificationType::PASSWORD_RESET => 'Reset your password.',
        };
    }

    private function body(NotificationContext $context): string
    {
        return match ($context->type) {
            NotificationType::WELCOME        => "Hi {$context->email},\n\nYour account has been created.\nUser ID: {$context->userId}",
            NotificationType::PASSWORD_RESET => "Hi {$context->email},\n\nA password reset was requested for your account.",
        };
    }
}
