<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200203091651 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE document (id INT NOT NULL, auteur_id INT NOT NULL, titre VARCHAR(255) NOT NULL, description TEXT NOT NULL, categorie VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D8698A7660BB6FE6 ON document (auteur_id)');
        $this->addSql('CREATE TABLE partie (id INT NOT NULL, document_id INT NOT NULL, partie_parent_id INT DEFAULT NULL, nom VARCHAR(255) NOT NULL, contenu TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_59B1F3DC33F7837 ON partie (document_id)');
        $this->addSql('CREATE INDEX IDX_59B1F3DF1406845 ON partie (partie_parent_id)');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A7660BB6FE6 FOREIGN KEY (auteur_id) REFERENCES utilisateur (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE partie ADD CONSTRAINT FK_59B1F3DC33F7837 FOREIGN KEY (document_id) REFERENCES document (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE partie ADD CONSTRAINT FK_59B1F3DF1406845 FOREIGN KEY (partie_parent_id) REFERENCES partie (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE partie DROP CONSTRAINT FK_59B1F3DC33F7837');
        $this->addSql('ALTER TABLE partie DROP CONSTRAINT FK_59B1F3DF1406845');
        $this->addSql('DROP TABLE document');
        $this->addSql('DROP TABLE partie');
    }
}
