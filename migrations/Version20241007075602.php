<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241007075602 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE tournament (id INT AUTO_INCREMENT NOT NULL, winner_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, status VARCHAR(50) NOT NULL, ranking_min VARCHAR(5) DEFAULT NULL, ranking_max VARCHAR(5) DEFAULT NULL, participants_max VARCHAR(20) NOT NULL, INDEX IDX_BD5FB8D95DFCD4B8 (winner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tournament_match (id INT AUTO_INCREMENT NOT NULL, tournament_id INT NOT NULL, player1_id INT NOT NULL, player2_id INT NOT NULL, winner_id INT DEFAULT NULL, round VARCHAR(255) NOT NULL, INDEX IDX_BB0D551C33D1A3E7 (tournament_id), INDEX IDX_BB0D551CC0990423 (player1_id), INDEX IDX_BB0D551CD22CABCD (player2_id), INDEX IDX_BB0D551C5DFCD4B8 (winner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tournament_registration (id INT AUTO_INCREMENT NOT NULL, tournament_id INT NOT NULL, player_id INT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_F42ADBF133D1A3E7 (tournament_id), INDEX IDX_F42ADBF199E6F5DF (player_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE tournament ADD CONSTRAINT FK_BD5FB8D95DFCD4B8 FOREIGN KEY (winner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE tournament_match ADD CONSTRAINT FK_BB0D551C33D1A3E7 FOREIGN KEY (tournament_id) REFERENCES tournament (id)');
        $this->addSql('ALTER TABLE tournament_match ADD CONSTRAINT FK_BB0D551CC0990423 FOREIGN KEY (player1_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE tournament_match ADD CONSTRAINT FK_BB0D551CD22CABCD FOREIGN KEY (player2_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE tournament_match ADD CONSTRAINT FK_BB0D551C5DFCD4B8 FOREIGN KEY (winner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE tournament_registration ADD CONSTRAINT FK_F42ADBF133D1A3E7 FOREIGN KEY (tournament_id) REFERENCES tournament (id)');
        $this->addSql('ALTER TABLE tournament_registration ADD CONSTRAINT FK_F42ADBF199E6F5DF FOREIGN KEY (player_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tournament DROP FOREIGN KEY FK_BD5FB8D95DFCD4B8');
        $this->addSql('ALTER TABLE tournament_match DROP FOREIGN KEY FK_BB0D551C33D1A3E7');
        $this->addSql('ALTER TABLE tournament_match DROP FOREIGN KEY FK_BB0D551CC0990423');
        $this->addSql('ALTER TABLE tournament_match DROP FOREIGN KEY FK_BB0D551CD22CABCD');
        $this->addSql('ALTER TABLE tournament_match DROP FOREIGN KEY FK_BB0D551C5DFCD4B8');
        $this->addSql('ALTER TABLE tournament_registration DROP FOREIGN KEY FK_F42ADBF133D1A3E7');
        $this->addSql('ALTER TABLE tournament_registration DROP FOREIGN KEY FK_F42ADBF199E6F5DF');
        $this->addSql('DROP TABLE tournament');
        $this->addSql('DROP TABLE tournament_match');
        $this->addSql('DROP TABLE tournament_registration');
    }
}
