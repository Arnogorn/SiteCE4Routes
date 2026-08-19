<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260819130024 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout de la légende et du texte personnalisables sur les photos vitrine (photo_site)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE photo_site ADD title VARCHAR(255) DEFAULT NULL, ADD description LONGTEXT DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE photo_site DROP title, DROP description
        SQL);
    }
}
