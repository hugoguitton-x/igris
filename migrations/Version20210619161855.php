<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210619161855 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE kaisel_user');
        $this->addSql('ALTER TABLE utilisateur ALTER username TYPE VARCHAR(180)');
        $this->addSql('ALTER TABLE utilisateur ALTER email TYPE VARCHAR(180)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1D1C63B3F85E0677 ON utilisateur (username)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1D1C63B3E7927C74 ON utilisateur (email)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE TABLE kaisel_user (id INT NOT NULL, username VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX kaisel_user_username_email_key ON kaisel_user (username, email)');
        $this->addSql('DROP INDEX UNIQ_1D1C63B3F85E0677');
        $this->addSql('DROP INDEX UNIQ_1D1C63B3E7927C74');
        $this->addSql('ALTER TABLE utilisateur ALTER username TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE utilisateur ALTER email TYPE VARCHAR(255)');
    }
}
