<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210306120757 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE depense_recurrente ADD compte_depense_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE depense_recurrente ADD CONSTRAINT FK_409DC53089CCCADD FOREIGN KEY (compte_depense_id) REFERENCES compte_depense (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_409DC53089CCCADD ON depense_recurrente (compte_depense_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE depense_recurrente DROP CONSTRAINT FK_409DC53089CCCADD');
        $this->addSql('DROP INDEX IDX_409DC53089CCCADD');
        $this->addSql('ALTER TABLE depense_recurrente DROP compte_depense_id');
    }
}
