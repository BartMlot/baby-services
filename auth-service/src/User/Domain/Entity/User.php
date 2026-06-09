<?php

declare(strict_types=1);

namespace App\User\Domain\Entity;

use App\User\Domain\Enum\UserStatus;
use App\User\Domain\Event\UserRegistered;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\HashedPassword;
use App\User\Domain\ValueObject\PhoneNumber;
use App\User\Domain\ValueObject\UserId;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'users')]
class User
{
    /** @var object[] */
    private array $domainEvents = [];

    #[ORM\Id]
    #[ORM\Column(type: 'guid')]
    private string $id;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private string $email;

    #[ORM\Column(type: 'string', length: 255)]
    private string $password;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private ?string $phoneNumber;

    #[ORM\Column(type: 'string', length: 20, enumType: UserStatus::class)]
    private UserStatus $status;

    private function __construct(
        string $id,
        string $email,
        string $password,
        ?string $phoneNumber,
        UserStatus $status,
    ) {
        $this->id = $id;
        $this->email = $email;
        $this->password = $password;
        $this->phoneNumber = $phoneNumber;
        $this->status = $status;
    }

    public static function register(
        UserId $id,
        Email $email,
        HashedPassword $password,
        ?PhoneNumber $phoneNumber = null,
    ): self {
        $user = new self(
            $id->value(),
            $email->value(),
            $password->value(),
            $phoneNumber?->value(),
            UserStatus::ACTIVE,
        );

        $user->domainEvents[] = new UserRegistered($id, $email, $phoneNumber);

        return $user;
    }

    /** @return object[] */
    public function pullDomainEvents(): array
    {
        $events = $this->domainEvents;
        $this->domainEvents = [];

        return $events;
    }

    public function getId(): UserId
    {
        return UserId::fromString($this->id);
    }

    public function getEmail(): Email
    {
        return new Email($this->email);
    }

    public function getPassword(): HashedPassword
    {
        return new HashedPassword($this->password);
    }

    public function getPhoneNumber(): ?PhoneNumber
    {
        return $this->phoneNumber !== null ? new PhoneNumber($this->phoneNumber) : null;
    }

    public function getStatus(): UserStatus
    {
        return $this->status;
    }

    public function isActive(): bool
    {
        return $this->status === UserStatus::ACTIVE;
    }
}
