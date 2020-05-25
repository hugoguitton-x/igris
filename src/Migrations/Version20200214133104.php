<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200214133104 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE language_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE last_chapter_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE language (id INT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE last_chapter (id INT NOT NULL, language_id INT NOT NULL, manga_id INT NOT NULL, number VARCHAR(255) NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9F0D896682F1BAF4 ON last_chapter (language_id)');
        $this->addSql('CREATE INDEX IDX_9F0D89667B6461 ON last_chapter (manga_id)');
        $this->addSql('ALTER TABLE last_chapter ADD CONSTRAINT FK_9F0D896682F1BAF4 FOREIGN KEY (language_id) REFERENCES language (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE last_chapter ADD CONSTRAINT FK_9F0D89667B6461 FOREIGN KEY (manga_id) REFERENCES manga (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE manga DROP last_chapter');
        $this->addSql('ALTER TABLE manga DROP language');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE last_chapter DROP CONSTRAINT FK_9F0D896682F1BAF4');
        $this->addSql('DROP SEQUENCE language_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE last_chapter_id_seq CASCADE');
        $this->addSql('DROP TABLE language');
        $this->addSql('DROP TABLE last_chapter');
        $this->addSql('ALTER TABLE manga ADD last_chapter VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE manga ADD language VARCHAR(255) NOT NULL');
    }
}
