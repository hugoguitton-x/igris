<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200701110132 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE chapter ADD manga_id INT NOT NULL');
        $this->addSql('ALTER TABLE chapter ADD CONSTRAINT FK_F981B52E7B6461 FOREIGN KEY (manga_id) REFERENCES manga (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_F981B52E7B6461 ON chapter (manga_id)');
        $this->addSql("INSERT INTO language_code(id, lang_code, libelle) VALUES (1, 'gb', 'English'), (2, 'fr', 'French')");
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE chapter DROP CONSTRAINT FK_F981B52E7B6461');
        $this->addSql('DROP INDEX IDX_F981B52E7B6461');
        $this->addSql('ALTER TABLE chapter DROP manga_id');
    }
}
