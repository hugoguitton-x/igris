<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200701104717 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE chapter_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE language_code_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE chapter (id INT NOT NULL, lang_code_id INT NOT NULL, chapter_id INT NOT NULL, number VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_F981B52E64BB6B3 ON chapter (lang_code_id)');
        $this->addSql('CREATE TABLE language_code (id INT NOT NULL, lang_code VARCHAR(255) NOT NULL, libelle VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE chapter ADD CONSTRAINT FK_F981B52E64BB6B3 FOREIGN KEY (lang_code_id) REFERENCES language_code (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE utilisateur ALTER avatar DROP DEFAULT');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE chapter DROP CONSTRAINT FK_F981B52E64BB6B3');
        $this->addSql('DROP SEQUENCE chapter_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE language_code_id_seq CASCADE');
        $this->addSql('DROP TABLE chapter');
        $this->addSql('DROP TABLE language_code');
        $this->addSql('ALTER TABLE utilisateur ALTER avatar SET DEFAULT \'default.png\'');
    }
}
