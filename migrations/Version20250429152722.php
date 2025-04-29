<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250429152722 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE calendrier (id INT AUTO_INCREMENT NOT NULL, contenu VARCHAR(255) NOT NULL, jour VARCHAR(15) NOT NULL, heure_debut INT NOT NULL, heure_fin INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE cheval (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, nom VARCHAR(255) NOT NULL, nom_pere VARCHAR(255) DEFAULT NULL, nom_mere VARCHAR(255) DEFAULT NULL, date_naissance DATE DEFAULT NULL COMMENT '(DC2Type:date_immutable)', lieu_naissance VARCHAR(255) DEFAULT NULL, photo VARCHAR(255) DEFAULT NULL, appartient_ecurie TINYINT(1) NOT NULL, infos LONGTEXT DEFAULT NULL, a_vendre TINYINT(1) NOT NULL, vendu TINYINT(1) NOT NULL, INDEX IDX_F286849DA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE competition (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, date_debut DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', date_fin DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', prix_transport INT DEFAULT NULL, prix_epreuve INT DEFAULT NULL, description LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE contact (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, tel VARCHAR(20) NOT NULL, description LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE ecurie_proprietaire (id INT AUTO_INCREMENT NOT NULL, prestation VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, prix INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE etat (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(15) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE famille (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE membre_famille (id INT AUTO_INCREMENT NOT NULL, famille_id INT NOT NULL, niveau_id INT NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, date_naissance DATE NOT NULL COMMENT '(DC2Type:date_immutable)', allergies VARCHAR(255) DEFAULT NULL, medecin_traitant VARCHAR(255) DEFAULT NULL, tel_medecin VARCHAR(20) DEFAULT NULL, droit_image TINYINT(1) NOT NULL, INDEX IDX_9654F0AE97A77B84 (famille_id), INDEX IDX_9654F0AEB3E9C81 (niveau_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE moniteur (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, UNIQUE INDEX UNIQ_B3EC8EBAA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE niveau (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE sortie (id INT AUTO_INCREMENT NOT NULL, moniteur_id INT NOT NULL, etat_id INT NOT NULL, titre VARCHAR(255) NOT NULL, date DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', duree INT NOT NULL, date_limite_inscription DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', nb_inscription_max INT NOT NULL, prix INT NOT NULL, infos LONGTEXT DEFAULT NULL, is_published TINYINT(1) NOT NULL, INDEX IDX_3C3FD3F2A234A5D3 (moniteur_id), INDEX IDX_3C3FD3F2D5E86FF (etat_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE sortie_niveau (sortie_id INT NOT NULL, niveau_id INT NOT NULL, INDEX IDX_4C2B39D7CC72D953 (sortie_id), INDEX IDX_4C2B39D7B3E9C81 (niveau_id), PRIMARY KEY(sortie_id, niveau_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, niveau_id INT NOT NULL, famille_id INT DEFAULT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(150) NOT NULL, date_naissance DATE NOT NULL COMMENT '(DC2Type:date_immutable)', adresse VARCHAR(255) NOT NULL, telephone VARCHAR(20) NOT NULL, photo VARCHAR(255) DEFAULT NULL, tel_pers_contact VARCHAR(20) DEFAULT NULL, allergies VARCHAR(255) DEFAULT NULL, medecin_traitant VARCHAR(255) DEFAULT NULL, tel_medecin VARCHAR(20) DEFAULT NULL, droit_image TINYINT(1) NOT NULL, actif TINYINT(1) NOT NULL, infos LONGTEXT DEFAULT NULL, INDEX IDX_8D93D649B3E9C81 (niveau_id), UNIQUE INDEX UNIQ_8D93D64997A77B84 (famille_id), UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', available_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', delivered_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE cheval ADD CONSTRAINT FK_F286849DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE membre_famille ADD CONSTRAINT FK_9654F0AE97A77B84 FOREIGN KEY (famille_id) REFERENCES famille (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE membre_famille ADD CONSTRAINT FK_9654F0AEB3E9C81 FOREIGN KEY (niveau_id) REFERENCES niveau (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE moniteur ADD CONSTRAINT FK_B3EC8EBAA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE sortie ADD CONSTRAINT FK_3C3FD3F2A234A5D3 FOREIGN KEY (moniteur_id) REFERENCES moniteur (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE sortie ADD CONSTRAINT FK_3C3FD3F2D5E86FF FOREIGN KEY (etat_id) REFERENCES etat (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE sortie_niveau ADD CONSTRAINT FK_4C2B39D7CC72D953 FOREIGN KEY (sortie_id) REFERENCES sortie (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE sortie_niveau ADD CONSTRAINT FK_4C2B39D7B3E9C81 FOREIGN KEY (niveau_id) REFERENCES niveau (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user ADD CONSTRAINT FK_8D93D649B3E9C81 FOREIGN KEY (niveau_id) REFERENCES niveau (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user ADD CONSTRAINT FK_8D93D64997A77B84 FOREIGN KEY (famille_id) REFERENCES famille (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE cheval DROP FOREIGN KEY FK_F286849DA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE membre_famille DROP FOREIGN KEY FK_9654F0AE97A77B84
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE membre_famille DROP FOREIGN KEY FK_9654F0AEB3E9C81
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE moniteur DROP FOREIGN KEY FK_B3EC8EBAA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE sortie DROP FOREIGN KEY FK_3C3FD3F2A234A5D3
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE sortie DROP FOREIGN KEY FK_3C3FD3F2D5E86FF
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE sortie_niveau DROP FOREIGN KEY FK_4C2B39D7CC72D953
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE sortie_niveau DROP FOREIGN KEY FK_4C2B39D7B3E9C81
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user DROP FOREIGN KEY FK_8D93D649B3E9C81
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user DROP FOREIGN KEY FK_8D93D64997A77B84
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE calendrier
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE cheval
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE competition
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE contact
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE ecurie_proprietaire
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE etat
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE famille
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE membre_famille
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE moniteur
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE niveau
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE sortie
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE sortie_niveau
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE messenger_messages
        SQL);
    }
}
