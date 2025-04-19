<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250414015604 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE favoris ADD CONSTRAINT FK_8933C432F7384557 FOREIGN KEY (id_produit) REFERENCES produit (id)');
        $this->addSql('CREATE INDEX IDX_8933C432F7384557 ON favoris (id_produit)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE favoris DROP FOREIGN KEY FK_8933C432F7384557');
        $this->addSql('DROP INDEX IDX_8933C432F7384557 ON favoris');
    }
}
