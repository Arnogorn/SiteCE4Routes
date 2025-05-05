<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250505101011 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE user_sortie (user_id INT NOT NULL, sortie_id INT NOT NULL, INDEX IDX_596DC8CFA76ED395 (user_id), INDEX IDX_596DC8CFCC72D953 (sortie_id), PRIMARY KEY(user_id, sortie_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_sortie ADD CONSTRAINT FK_596DC8CFA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_sortie ADD CONSTRAINT FK_596DC8CFCC72D953 FOREIGN KEY (sortie_id) REFERENCES sortie (id) ON DELETE CASCADE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE user_sortie DROP FOREIGN KEY FK_596DC8CFA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_sortie DROP FOREIGN KEY FK_596DC8CFCC72D953
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user_sortie
        SQL);
    }
}
