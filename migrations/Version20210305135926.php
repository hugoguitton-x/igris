<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210305135926 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE compte_depense_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE depense_recurrente_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE compte_depense (id INT NOT NULL, utilisateur_id INT NOT NULL, solde DOUBLE PRECISION NOT NULL, nom VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_8C62AB01FB88E14F ON compte_depense (utilisateur_id)');
        $this->addSql('CREATE TABLE depense_recurrente (id INT NOT NULL, categorie_id INT NOT NULL, montant DOUBLE PRECISION NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_409DC530BCF5E72D ON depense_recurrente (categorie_id)');
        $this->addSql('ALTER TABLE compte_depense ADD CONSTRAINT FK_8C62AB01FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE depense_recurrente ADD CONSTRAINT FK_409DC530BCF5E72D FOREIGN KEY (categorie_id) REFERENCES categorie_depense (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE categorie_depense DROP recurrent');
        $this->addSql('ALTER TABLE depense ADD compte_depense_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE depense ADD categorie_id INT NOT NULL');
        $this->addSql('ALTER TABLE depense DROP solde_initial');
        $this->addSql('ALTER TABLE depense DROP solde_final');
        $this->addSql('ALTER TABLE depense ADD CONSTRAINT FK_3405975789CCCADD FOREIGN KEY (compte_depense_id) REFERENCES compte_depense (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE depense ADD CONSTRAINT FK_34059757BCF5E72D FOREIGN KEY (categorie_id) REFERENCES categorie_depense (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_3405975789CCCADD ON depense (compte_depense_id)');
        $this->addSql('CREATE INDEX IDX_34059757BCF5E72D ON depense (categorie_id)');
        $this->addSql('ALTER TABLE utilisateur DROP solde');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE depense DROP CONSTRAINT FK_3405975789CCCADD');
        $this->addSql('DROP SEQUENCE compte_depense_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE depense_recurrente_id_seq CASCADE');
        $this->addSql('DROP TABLE compte_depense');
        $this->addSql('DROP TABLE depense_recurrente');
        $this->addSql('ALTER TABLE utilisateur ADD solde DOUBLE PRECISION DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE categorie_depense ADD recurrent BOOLEAN DEFAULT \'false\' NOT NULL');
        $this->addSql('ALTER TABLE depense DROP CONSTRAINT FK_34059757BCF5E72D');
        $this->addSql('DROP INDEX IDX_3405975789CCCADD');
        $this->addSql('DROP INDEX IDX_34059757BCF5E72D');
        $this->addSql('ALTER TABLE depense ADD solde_initial DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE depense ADD solde_final DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE depense DROP compte_depense_id');
        $this->addSql('ALTER TABLE depense DROP categorie_id');
    }
}
