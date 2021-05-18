<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210317133025 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE language_code ADD twitter_flag VARCHAR(255)');
        $this->addSql("UPDATE language_code SET twitter_flag = '🇫🇷' WHERE lang_code ='fr'");
        $this->addSql("UPDATE language_code SET twitter_flag = '🇬🇧' WHERE lang_code ='gb'");
        $this->addSql('ALTER TABLE language_code ALTER COLUMN twitter_flag SET NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE language_code DROP twitter_flag');
    }
}
