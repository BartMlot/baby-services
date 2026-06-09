<?php

declare(strict_types=1);

namespace App\Notification\Domain\Entity;

use App\Notification\Domain\Enum\NotificationType;
use App\Notification\Domain\ValueObject\NotificationId;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'notification_logs')]
class NotificationLog
{
    #[ORM\Id]
    #[ORM\Column(type: 'guid')]
    private string $id;

    #[ORM\Column(type: 'guid')]
    private string $userId;

    #[ORM\Column(type: 'string', length: 255)]
    private string $email;

    #[ORM\Column(type: 'string', length: 50, enumType: NotificationType::class)]
    private NotificationType $type;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $sentAt;

    private function __construct(
        string $id,
        string $userId,
        string $email,
        NotificationType $type,
        \DateTimeImmutable $sentAt,
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->email = $email;
        $this->type = $type;
        $this->sentAt = $sentAt;
    }

    public static function create(
        NotificationId $id,
        string $userId,
        string $email,
        NotificationType $type,
    ): self {
        return new self(
            $id->value(),
            $userId,
            $email,
            $type,
            new \DateTimeImmutable(),
        );
    }

    public function getId(): NotificationId
    {
        return NotificationId::fromString($this->id);
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getType(): NotificationType
    {
        return $this->type;
    }

    public function getSentAt(): \DateTimeImmutable
    {
        return $this->sentAt;
    }
}
