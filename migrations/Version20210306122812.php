<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210306122812 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE depense ADD depense_recurrente_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE depense ADD CONSTRAINT FK_340597577A19EC12 FOREIGN KEY (depense_recurrente_id) REFERENCES depense_recurrente (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_340597577A19EC12 ON depense (depense_recurrente_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE depense DROP CONSTRAINT FK_340597577A19EC12');
        $this->addSql('DROP INDEX IDX_340597577A19EC12');
        $this->addSql('ALTER TABLE depense DROP depense_recurrente_id');
    }
}
