<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Ajoute le consentement explicite au traitement des données de santé (RGPD)
 * sur les comptes utilisateurs et les membres de famille.
 */
final class Version20260818120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute consentement_donnees_sante et consentement_donnees_sante_at sur user et membre_famille';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE user
                ADD consentement_donnees_sante TINYINT(1) NOT NULL DEFAULT 0,
                ADD consentement_donnees_sante_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE membre_famille
                ADD consentement_donnees_sante TINYINT(1) NOT NULL DEFAULT 0,
                ADD consentement_donnees_sante_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE user
                DROP consentement_donnees_sante,
                DROP consentement_donnees_sante_at
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE membre_famille
                DROP consentement_donnees_sante,
                DROP consentement_donnees_sante_at
        SQL);
    }
}
