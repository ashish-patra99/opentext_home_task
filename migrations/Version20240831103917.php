<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240831103917 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE job_file (id INT AUTO_INCREMENT NOT NULL, api_job_id_id INT NOT NULL, file_name VARCHAR(255) NOT NULL, scanned_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', ci_upload_id VARCHAR(64) DEFAULT NULL, status VARCHAR(255) NOT NULL, remark VARCHAR(255) DEFAULT NULL, INDEX IDX_B5340513CC865C (api_job_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE job_notification (id INT AUTO_INCREMENT NOT NULL, api_job_id_id INT NOT NULL, job_type VARCHAR(255) NOT NULL, sent_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', recepient VARCHAR(255) DEFAULT NULL, message VARCHAR(1024) NOT NULL, status TINYINT(1) NOT NULL, INDEX IDX_B037E3E53CC865C (api_job_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE job_file ADD CONSTRAINT FK_B5340513CC865C FOREIGN KEY (api_job_id_id) REFERENCES api_jobs (id)');
        $this->addSql('ALTER TABLE job_notification ADD CONSTRAINT FK_B037E3E53CC865C FOREIGN KEY (api_job_id_id) REFERENCES api_jobs (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE job_file DROP FOREIGN KEY FK_B5340513CC865C');
        $this->addSql('ALTER TABLE job_notification DROP FOREIGN KEY FK_B037E3E53CC865C');
        $this->addSql('DROP TABLE job_file');
        $this->addSql('DROP TABLE job_notification');
    }
}
