<?php

declare(strict_types=1);

namespace App\Notification\Infrastructure\Messaging\Serializer;

use App\Notification\Infrastructure\Messaging\Message\UserRegisteredMessage;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\MessageDecodingFailedException;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

/**
 * Bidirectional serializer for cross-service AMQP messages.
 * encode() is required so Messenger can re-queue messages to the failure transport.
 */
final class AmqpMessageSerializer implements SerializerInterface
{
    public function decode(array $encodedEnvelope): Envelope
    {
        $body = json_decode($encodedEnvelope['body'] ?? '{}', true, flags: JSON_THROW_ON_ERROR);
        $type = $encodedEnvelope['headers']['type'] ?? '';

        $message = match (true) {
            str_contains($type, 'UserRegisteredMessage') => new UserRegisteredMessage(
                userId: $body['userId'] ?? throw new MessageDecodingFailedException('Missing "userId".'),
                email: $body['email'] ?? throw new MessageDecodingFailedException('Missing "email".'),
                occurredAt: $body['occurredAt'] ?? throw new MessageDecodingFailedException('Missing "occurredAt".'),
                phoneNumber: $body['phoneNumber'] ?? null,
            ),
            default => throw new MessageDecodingFailedException(
                sprintf('No decoder for message type: "%s".', $type)
            ),
        };

        return new Envelope($message);
    }

    public function encode(Envelope $envelope): array
    {
        $message = $envelope->getMessage();

        return match (true) {
            $message instanceof UserRegisteredMessage => [
                'body'    => json_encode([
                    'userId'      => $message->userId,
                    'email'       => $message->email,
                    'occurredAt'  => $message->occurredAt,
                    'phoneNumber' => $message->phoneNumber,
                ], JSON_THROW_ON_ERROR),
                'headers' => [
                    'type'         => 'App\User\Infrastructure\Messaging\Message\UserRegisteredMessage',
                    'Content-Type' => 'application/json',
                ],
            ],
            default => throw new \LogicException(
                sprintf('No encoder defined for message type: %s', $message::class)
            ),
        };
    }
}
