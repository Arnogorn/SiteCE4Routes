<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260819124311 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Ajout de la table photo_site (photos vitrine modifiables par l'admin) et de ses emplacements connus";
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE photo_site (id INT AUTO_INCREMENT NOT NULL, slug VARCHAR(100) NOT NULL, label VARCHAR(255) NOT NULL, filename VARCHAR(255) DEFAULT NULL, updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', UNIQUE INDEX UNIQ_E872BB10989D9B62 (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);

        // Emplacements connus (registre App\Service\PhotoSiteRegistry) : filename NULL = photo d'origine du site
        $this->addSql(<<<'SQL'
            INSERT INTO photo_site (slug, label) VALUES
                ('accueil_dressage', 'Accueil - Excellence en dressage'),
                ('accueil_obstacles', "Accueil - Saut d'obstacles en compétitions"),
                ('ecurie_manege', 'Écurie propriétaire - Manège couvert'),
                ('ecurie_marcheur', 'Écurie propriétaire - Marcheur automatique'),
                ('commerce_dressage', 'Commerce - Dressage en compétition avec Lorraine'),
                ('commerce_obstacles', "Commerce - Saut d'obstacles avec Damien"),
                ('contact_obstacles', "Contact - Saut d'obstacles en compétition avec Damien"),
                ('contact_dressage', 'Contact - Dressage en compétition avec Lorraine'),
                ('competition_obstacles', "Compétition - Saut d'obstacles en compétition"),
                ('competition_ambiance', 'Compétition - Ambiance des concours'),
                ('tarifs_depart', 'Tarifs / Poney club - Préparation et départ à cheval'),
                ('tarifs_manege', "Tarifs / Poney club - Cours d'équitation en manège"),
                ('sortie_lac', 'Sorties - Balade au lac de Trémelin'),
                ('sortie_foret', 'Sorties - Balade en forêt')
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP TABLE photo_site
        SQL);
    }
}
