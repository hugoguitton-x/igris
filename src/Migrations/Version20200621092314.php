<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200621092314 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE avis_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE document_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE etat_tache_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE language_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE last_chapter_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE manga_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE partie_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE serie_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE tache_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE utilisateur_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE avis (id INT NOT NULL, utilisateur_id INT NOT NULL, serie_id INT NOT NULL, note INT NOT NULL, commentaire TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_8F91ABF0FB88E14F ON avis (utilisateur_id)');
        $this->addSql('CREATE INDEX IDX_8F91ABF0D94388BD ON avis (serie_id)');
        $this->addSql('CREATE TABLE document (id INT NOT NULL, auteur_id INT NOT NULL, titre VARCHAR(255) NOT NULL, description TEXT NOT NULL, categorie VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D8698A7660BB6FE6 ON document (auteur_id)');
        $this->addSql('CREATE TABLE etat_tache (id INT NOT NULL, libelle VARCHAR(255) NOT NULL, code_bootsrap VARCHAR(255) NOT NULL, nom VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE language (id INT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE last_chapter (id INT NOT NULL, manga_id INT NOT NULL, language_id INT NOT NULL, number VARCHAR(255) NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_9F0D89667B6461 ON last_chapter (manga_id)');
        $this->addSql('CREATE INDEX IDX_9F0D896682F1BAF4 ON last_chapter (language_id)');
        $this->addSql('CREATE TABLE manga (id INT NOT NULL, name VARCHAR(255) NOT NULL, image VARCHAR(255) NOT NULL, url VARCHAR(255) NOT NULL, rss VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE partie (id INT NOT NULL, document_id INT NOT NULL, partie_parent_id INT DEFAULT NULL, nom VARCHAR(255) NOT NULL, contenu TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, numero INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_59B1F3DC33F7837 ON partie (document_id)');
        $this->addSql('CREATE INDEX IDX_59B1F3DF1406845 ON partie (partie_parent_id)');
        $this->addSql('CREATE TABLE serie (id INT NOT NULL, nom VARCHAR(255) NOT NULL, image VARCHAR(255) NOT NULL, synopsis TEXT NOT NULL, lien VARCHAR(255) NOT NULL, nombre_episodes INT NOT NULL, duree_episode INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, note_moyenne DOUBLE PRECISION NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE tache (id INT NOT NULL, etat_id INT DEFAULT NULL, nom VARCHAR(255) NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, contenu TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_93872075D5E86FF ON tache (etat_id)');
        $this->addSql('CREATE TABLE utilisateur (id INT NOT NULL, username VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, firstname VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, last_login TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, avatar VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE avis ADD CONSTRAINT FK_8F91ABF0FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE avis ADD CONSTRAINT FK_8F91ABF0D94388BD FOREIGN KEY (serie_id) REFERENCES serie (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A7660BB6FE6 FOREIGN KEY (auteur_id) REFERENCES utilisateur (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE last_chapter ADD CONSTRAINT FK_9F0D89667B6461 FOREIGN KEY (manga_id) REFERENCES manga (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE last_chapter ADD CONSTRAINT FK_9F0D896682F1BAF4 FOREIGN KEY (language_id) REFERENCES language (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE partie ADD CONSTRAINT FK_59B1F3DC33F7837 FOREIGN KEY (document_id) REFERENCES document (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE partie ADD CONSTRAINT FK_59B1F3DF1406845 FOREIGN KEY (partie_parent_id) REFERENCES partie (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tache ADD CONSTRAINT FK_93872075D5E86FF FOREIGN KEY (etat_id) REFERENCES etat_tache (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE partie DROP CONSTRAINT FK_59B1F3DC33F7837');
        $this->addSql('ALTER TABLE tache DROP CONSTRAINT FK_93872075D5E86FF');
        $this->addSql('ALTER TABLE last_chapter DROP CONSTRAINT FK_9F0D896682F1BAF4');
        $this->addSql('ALTER TABLE last_chapter DROP CONSTRAINT FK_9F0D89667B6461');
        $this->addSql('ALTER TABLE partie DROP CONSTRAINT FK_59B1F3DF1406845');
        $this->addSql('ALTER TABLE avis DROP CONSTRAINT FK_8F91ABF0D94388BD');
        $this->addSql('ALTER TABLE avis DROP CONSTRAINT FK_8F91ABF0FB88E14F');
        $this->addSql('ALTER TABLE document DROP CONSTRAINT FK_D8698A7660BB6FE6');
        $this->addSql('DROP SEQUENCE avis_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE document_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE etat_tache_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE language_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE last_chapter_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE manga_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE partie_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE serie_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE tache_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE utilisateur_id_seq CASCADE');
        $this->addSql('DROP TABLE avis');
        $this->addSql('DROP TABLE document');
        $this->addSql('DROP TABLE etat_tache');
        $this->addSql('DROP TABLE language');
        $this->addSql('DROP TABLE last_chapter');
        $this->addSql('DROP TABLE manga');
        $this->addSql('DROP TABLE partie');
        $this->addSql('DROP TABLE serie');
        $this->addSql('DROP TABLE tache');
        $this->addSql('DROP TABLE utilisateur');
    }
}
