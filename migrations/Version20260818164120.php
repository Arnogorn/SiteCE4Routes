<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260818164120 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rattrapage schéma : tables inscription/historique_paiement manquantes, structure paiement à jour (vide les données de test incompatibles), no_licence sur user/membre_famille';
    }

    public function up(Schema $schema): void
    {
        // Les lignes existantes sont des données de test issues d'un ancien schéma
        // (statuts en anglais incompatibles avec Paiement::STATUT_*, aucune Inscription
        // associée puisque cette table n'existait pas encore). On repart propre plutôt
        // que de forcer des colonnes NOT NULL sans valeur cohérente sur ces lignes.
        $this->addSql('TRUNCATE TABLE paiement');

        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE historique_paiement (id INT AUTO_INCREMENT NOT NULL, utilisateur_id INT NOT NULL, sortie_id INT DEFAULT NULL, type VARCHAR(50) NOT NULL, date DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', message LONGTEXT DEFAULT NULL, INDEX IDX_710402ECFB88E14F (utilisateur_id), INDEX IDX_710402ECCC72D953 (sortie_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE inscription (id INT AUTO_INCREMENT NOT NULL, paiement_id INT NOT NULL, sortie_id INT NOT NULL, inscrit_par_id INT NOT NULL, utilisateur_id INT DEFAULT NULL, membre_famille_id INT DEFAULT NULL, nom VARCHAR(100) NOT NULL, date_inscription DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_5E90F6D62A4C4478 (paiement_id), INDEX IDX_5E90F6D6CC72D953 (sortie_id), INDEX IDX_5E90F6D6957A3F1A (inscrit_par_id), INDEX IDX_5E90F6D6FB88E14F (utilisateur_id), INDEX IDX_5E90F6D6CD1DC19C (membre_famille_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE historique_paiement ADD CONSTRAINT FK_710402ECFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE historique_paiement ADD CONSTRAINT FK_710402ECCC72D953 FOREIGN KEY (sortie_id) REFERENCES sortie (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE inscription ADD CONSTRAINT FK_5E90F6D62A4C4478 FOREIGN KEY (paiement_id) REFERENCES paiement (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE inscription ADD CONSTRAINT FK_5E90F6D6CC72D953 FOREIGN KEY (sortie_id) REFERENCES sortie (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE inscription ADD CONSTRAINT FK_5E90F6D6957A3F1A FOREIGN KEY (inscrit_par_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE inscription ADD CONSTRAINT FK_5E90F6D6FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE inscription ADD CONSTRAINT FK_5E90F6D6CD1DC19C FOREIGN KEY (membre_famille_id) REFERENCES membre_famille (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE contact CHANGE tel tel VARCHAR(20) DEFAULT NULL, CHANGE photo photo VARCHAR(255) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE membre_famille ADD no_licence VARCHAR(50) DEFAULT NULL, CHANGE consentement_donnees_sante consentement_donnees_sante TINYINT(1) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE paiement DROP FOREIGN KEY FK_B1DC7A1EA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_B1DC7A1EA76ED395 ON paiement
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE paiement ADD stripe_session_id VARCHAR(255) NOT NULL, ADD participants INT NOT NULL, ADD statut VARCHAR(255) NOT NULL, ADD refunded_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', DROP status, DROP currency, DROP metadata, CHANGE updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', CHANGE user_id utilisateur_id INT NOT NULL, CHANGE paid_at confirmed_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', CHANGE stripe_checkout_session_id stripe_charge_id VARCHAR(255) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE paiement ADD CONSTRAINT FK_B1DC7A1EFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_B1DC7A1EFB88E14F ON paiement (utilisateur_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user ADD no_licence VARCHAR(50) DEFAULT NULL, CHANGE consentement_donnees_sante consentement_donnees_sante TINYINT(1) NOT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE historique_paiement DROP FOREIGN KEY FK_710402ECFB88E14F
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE historique_paiement DROP FOREIGN KEY FK_710402ECCC72D953
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE inscription DROP FOREIGN KEY FK_5E90F6D62A4C4478
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE inscription DROP FOREIGN KEY FK_5E90F6D6CC72D953
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE inscription DROP FOREIGN KEY FK_5E90F6D6957A3F1A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE inscription DROP FOREIGN KEY FK_5E90F6D6FB88E14F
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE inscription DROP FOREIGN KEY FK_5E90F6D6CD1DC19C
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE historique_paiement
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE inscription
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE contact CHANGE photo photo VARCHAR(255) NOT NULL, CHANGE tel tel VARCHAR(20) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE membre_famille DROP no_licence, CHANGE consentement_donnees_sante consentement_donnees_sante TINYINT(1) DEFAULT 0 NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE paiement DROP FOREIGN KEY FK_B1DC7A1EFB88E14F
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_B1DC7A1EFB88E14F ON paiement
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE paiement ADD user_id INT NOT NULL, ADD status VARCHAR(20) NOT NULL, ADD currency VARCHAR(3) NOT NULL, ADD paid_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', ADD metadata LONGTEXT DEFAULT NULL, DROP utilisateur_id, DROP stripe_session_id, DROP participants, DROP statut, DROP confirmed_at, DROP refunded_at, CHANGE updated_at updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', CHANGE stripe_charge_id stripe_checkout_session_id VARCHAR(255) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE paiement ADD CONSTRAINT FK_B1DC7A1EA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_B1DC7A1EA76ED395 ON paiement (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user DROP no_licence, CHANGE consentement_donnees_sante consentement_donnees_sante TINYINT(1) DEFAULT 0 NOT NULL
        SQL);
    }
}
