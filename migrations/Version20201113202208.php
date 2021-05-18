<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201113202208 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE follow_manga_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE follow_manga (id INT NOT NULL, manga_id INT NOT NULL, utilisateur_id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_63BE74B97B6461 ON follow_manga (manga_id)');
        $this->addSql('CREATE INDEX IDX_63BE74B9FB88E14F ON follow_manga (utilisateur_id)');
        $this->addSql('ALTER TABLE follow_manga ADD CONSTRAINT FK_63BE74B97B6461 FOREIGN KEY (manga_id) REFERENCES manga (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE follow_manga ADD CONSTRAINT FK_63BE74B9FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE follow_manga_id_seq CASCADE');
        $this->addSql('DROP TABLE follow_manga');
    }
}
