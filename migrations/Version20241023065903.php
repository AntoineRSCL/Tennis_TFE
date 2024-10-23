<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241023065903 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE coach_language (coach_id INT NOT NULL, language_id INT NOT NULL, INDEX IDX_AC6768FF3C105691 (coach_id), INDEX IDX_AC6768FF82F1BAF4 (language_id), PRIMARY KEY(coach_id, language_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE coach_language ADD CONSTRAINT FK_AC6768FF3C105691 FOREIGN KEY (coach_id) REFERENCES coach (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE coach_language ADD CONSTRAINT FK_AC6768FF82F1BAF4 FOREIGN KEY (language_id) REFERENCES language (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE coach ADD flag_image VARCHAR(255) DEFAULT NULL, CHANGE languages_spoken `function` VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE coach_language DROP FOREIGN KEY FK_AC6768FF3C105691');
        $this->addSql('ALTER TABLE coach_language DROP FOREIGN KEY FK_AC6768FF82F1BAF4');
        $this->addSql('DROP TABLE coach_language');
        $this->addSql('ALTER TABLE coach DROP flag_image, CHANGE `function` languages_spoken VARCHAR(255) NOT NULL');
    }
}
