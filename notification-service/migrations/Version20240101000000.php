<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240101000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create notification_logs table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE notification_logs (
            id VARCHAR(36) NOT NULL,
            user_id VARCHAR(36) NOT NULL,
            email VARCHAR(255) NOT NULL,
            type VARCHAR(50) NOT NULL,
            sent_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            PRIMARY KEY(id)
        )');
        $this->addSql('COMMENT ON COLUMN notification_logs.sent_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE INDEX IDX_NOTIFICATION_USER ON notification_logs (user_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE notification_logs');
    }
}
