<?php

declare(strict_types=1);

namespace App\Notification\Infrastructure\Persistence;

use App\Notification\Application\Port\NotificationLogRepositoryPort;
use App\Notification\Domain\Entity\NotificationLog;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineNotificationLogRepository implements NotificationLogRepositoryPort
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {}

    public function save(NotificationLog $log): void
    {
        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }

    public function findRecent(int $limit): array
    {
        return $this->entityManager
            ->createQueryBuilder()
            ->select('n')
            ->from(NotificationLog::class, 'n')
            ->orderBy('n.sentAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
