<?php

declare(strict_types=1);

namespace App\Tests\Unit\Notification\Infrastructure\Messaging\Serializer;

use App\Notification\Infrastructure\Messaging\Message\UserRegisteredMessage;
use App\Notification\Infrastructure\Messaging\Serializer\AmqpMessageSerializer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\MessageDecodingFailedException;

final class AmqpMessageSerializerTest extends TestCase
{
    private AmqpMessageSerializer $serializer;

    protected function setUp(): void
    {
        $this->serializer = new AmqpMessageSerializer();
    }

    // --- decode ---

    public function testDecodesUserRegisteredMessageWithPhone(): void
    {
        $envelope = $this->serializer->decode([
            'body' => json_encode([
                'userId'      => 'abc-123',
                'email'       => 'user@example.com',
                'occurredAt'  => '2024-01-01T00:00:00+00:00',
                'phoneNumber' => '+48123456789',
            ]),
            'headers' => ['type' => 'App\User\Infrastructure\Messaging\Message\UserRegisteredMessage'],
        ]);

        /** @var UserRegisteredMessage $msg */
        $msg = $envelope->getMessage();

        $this->assertInstanceOf(UserRegisteredMessage::class, $msg);
        $this->assertSame('abc-123', $msg->userId);
        $this->assertSame('+48123456789', $msg->phoneNumber);
    }

    public function testDecodesUserRegisteredMessageWithoutPhone(): void
    {
        $envelope = $this->serializer->decode([
            'body' => json_encode([
                'userId'     => 'abc-123',
                'email'      => 'user@example.com',
                'occurredAt' => '2024-01-01T00:00:00+00:00',
            ]),
            'headers' => ['type' => 'App\User\Infrastructure\Messaging\Message\UserRegisteredMessage'],
        ]);

        $this->assertNull($envelope->getMessage()->phoneNumber);
    }

    public function testThrowsOnUnknownMessageType(): void
    {
        $this->expectException(MessageDecodingFailedException::class);

        $this->serializer->decode([
            'body'    => '{}',
            'headers' => ['type' => 'App\Some\UnknownMessage'],
        ]);
    }

    public function testThrowsOnMissingRequiredField(): void
    {
        $this->expectException(MessageDecodingFailedException::class);

        $this->serializer->decode([
            'body'    => json_encode(['email' => 'user@example.com']),
            'headers' => ['type' => 'App\User\Infrastructure\Messaging\Message\UserRegisteredMessage'],
        ]);
    }

    // --- encode (required for retry / failure transport) ---

    public function testEncodesUserRegisteredMessage(): void
    {
        $message  = new UserRegisteredMessage('abc-123', 'user@example.com', '2024-01-01T00:00:00+00:00', '+48123456789');
        $envelope = new Envelope($message);

        $encoded = $this->serializer->encode($envelope);

        $this->assertArrayHasKey('body', $encoded);
        $this->assertArrayHasKey('headers', $encoded);

        $body = json_decode($encoded['body'], true);
        $this->assertSame('abc-123', $body['userId']);
        $this->assertSame('+48123456789', $body['phoneNumber']);
        $this->assertStringContainsString('UserRegisteredMessage', $encoded['headers']['type']);
    }

    public function testEncodeThrowsForUnknownMessage(): void
    {
        $this->expectException(\LogicException::class);

        $this->serializer->encode(new Envelope(new \stdClass()));
    }

    // --- round-trip ---

    public function testDecodeAfterEncodeReturnsEquivalentMessage(): void
    {
        $original = new UserRegisteredMessage('abc-123', 'user@example.com', '2024-01-01T00:00:00+00:00');
        $encoded  = $this->serializer->encode(new Envelope($original));
        $decoded  = $this->serializer->decode($encoded)->getMessage();

        /** @var UserRegisteredMessage $decoded */
        $this->assertSame($original->userId, $decoded->userId);
        $this->assertSame($original->email, $decoded->email);
        $this->assertNull($decoded->phoneNumber);
    }
}
