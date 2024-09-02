<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240831090920 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE api_jobs (id INT AUTO_INCREMENT NOT NULL, job_id INT NOT NULL, debricked_upload_id VARCHAR(255) DEFAULT NULL, request_started_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', request_completed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', request_json JSON DEFAULT NULL, response_json JSON DEFAULT NULL, api_job_type VARCHAR(64) DEFAULT NULL, api_job_end_point VARCHAR(255) DEFAULT NULL, notification_sent TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE api_jobs');
    }
}
