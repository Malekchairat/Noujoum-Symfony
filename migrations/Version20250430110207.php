<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250430110207 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE commande DROP INDEX fk_commande_panier, ADD UNIQUE INDEX UNIQ_6EEAA67D2FBB81F (id_panier)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commande DROP FOREIGN KEY fk_commande_panier
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commande DROP FOREIGN KEY fk_commande_user
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX fk_commande_user ON commande
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commande CHANGE code_postal code_postal VARCHAR(255) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67D2FBB81F FOREIGN KEY (id_panier) REFERENCES panier (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE evenement ADD slug VARCHAR(255) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE feedback CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE commentaire commentaire LONGTEXT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE panier DROP FOREIGN KEY fk_panier_produit
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE panier DROP FOREIGN KEY fk_panier_user
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX fk_panier_user ON panier
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE panier DROP FOREIGN KEY fk_panier_produit
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE panier ADD CONSTRAINT FK_24CC0DF2F7384557 FOREIGN KEY (id_produit) REFERENCES produit (id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX fk_panier_produit ON panier
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_24CC0DF2F7384557 ON panier (id_produit)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE panier ADD CONSTRAINT fk_panier_produit FOREIGN KEY (id_produit) REFERENCES produit (id) ON DELETE SET NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE produit CHANGE prix prix INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reclamation CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE description description LONGTEXT NOT NULL, CHANGE statut statut VARCHAR(255) NOT NULL, CHANGE priorite priorite VARCHAR(255) NOT NULL, CHANGE user_id user_id INT NOT NULL, CHANGE answer answer VARCHAR(500) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ticket CHANGE qr_code qr_code VARCHAR(20000) DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE commande DROP INDEX UNIQ_6EEAA67D2FBB81F, ADD INDEX fk_commande_panier (id_panier)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67D2FBB81F
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commande CHANGE code_postal code_postal INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commande ADD CONSTRAINT fk_commande_panier FOREIGN KEY (id_panier) REFERENCES panier (id) ON DELETE SET NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commande ADD CONSTRAINT fk_commande_user FOREIGN KEY (id_user) REFERENCES user (id_user) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX fk_commande_user ON commande (id_user)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE evenement DROP slug
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE feedback CHANGE id id INT NOT NULL, CHANGE commentaire commentaire TEXT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE panier DROP FOREIGN KEY FK_24CC0DF2F7384557
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE panier DROP FOREIGN KEY FK_24CC0DF2F7384557
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE panier ADD CONSTRAINT fk_panier_produit FOREIGN KEY (id_produit) REFERENCES produit (id) ON DELETE SET NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE panier ADD CONSTRAINT fk_panier_user FOREIGN KEY (id_user) REFERENCES user (id_user) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX fk_panier_user ON panier (id_user)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_24cc0df2f7384557 ON panier
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX fk_panier_produit ON panier (id_produit)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE panier ADD CONSTRAINT FK_24CC0DF2F7384557 FOREIGN KEY (id_produit) REFERENCES produit (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE produit CHANGE prix prix DOUBLE PRECISION NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reclamation CHANGE id id INT NOT NULL, CHANGE description description TEXT NOT NULL, CHANGE statut statut VARCHAR(255) DEFAULT NULL, CHANGE priorite priorite VARCHAR(255) DEFAULT NULL, CHANGE user_id user_id INT DEFAULT NULL, CHANGE answer answer VARCHAR(500) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ticket CHANGE qr_code qr_code MEDIUMTEXT DEFAULT NULL
        SQL);
    }
}
