<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200704185012 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE partie DROP CONSTRAINT fk_59b1f3dc33f7837');
        $this->addSql('ALTER TABLE partie DROP CONSTRAINT fk_59b1f3df1406845');
        $this->addSql('ALTER TABLE tache DROP CONSTRAINT fk_93872075d5e86ff');
        $this->addSql('DROP SEQUENCE document_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE etat_tache_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE partie_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE tache_id_seq CASCADE');
        $this->addSql('DROP TABLE document');
        $this->addSql('DROP TABLE partie');
        $this->addSql('DROP TABLE etat_tache');
        $this->addSql('DROP TABLE tache');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SEQUENCE document_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE etat_tache_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE partie_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE tache_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE document (id INT NOT NULL, auteur_id INT NOT NULL, titre VARCHAR(255) NOT NULL, description TEXT NOT NULL, categorie VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_d8698a7660bb6fe6 ON document (auteur_id)');
        $this->addSql('CREATE TABLE partie (id INT NOT NULL, document_id INT NOT NULL, partie_parent_id INT DEFAULT NULL, nom VARCHAR(255) NOT NULL, contenu TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, numero INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_59b1f3dc33f7837 ON partie (document_id)');
        $this->addSql('CREATE INDEX idx_59b1f3df1406845 ON partie (partie_parent_id)');
        $this->addSql('CREATE TABLE etat_tache (id INT NOT NULL, libelle VARCHAR(255) NOT NULL, code_bootsrap VARCHAR(255) NOT NULL, nom VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE tache (id INT NOT NULL, etat_id INT DEFAULT NULL, nom VARCHAR(255) NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, contenu TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_93872075d5e86ff ON tache (etat_id)');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT fk_d8698a7660bb6fe6 FOREIGN KEY (auteur_id) REFERENCES utilisateur (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE partie ADD CONSTRAINT fk_59b1f3dc33f7837 FOREIGN KEY (document_id) REFERENCES document (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE partie ADD CONSTRAINT fk_59b1f3df1406845 FOREIGN KEY (partie_parent_id) REFERENCES partie (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tache ADD CONSTRAINT fk_93872075d5e86ff FOREIGN KEY (etat_id) REFERENCES etat_tache (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
