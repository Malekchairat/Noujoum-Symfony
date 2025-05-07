<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250426192312 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE album_image (id INT AUTO_INCREMENT NOT NULL, produit_id INT NOT NULL, image_path VARCHAR(255) NOT NULL, INDEX IDX_B3854E79F347EFB (produit_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE evenement (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, date_debut DATETIME NOT NULL, date_fin DATETIME NOT NULL, lieu VARCHAR(255) NOT NULL, prix DOUBLE PRECISION NOT NULL, type_e VARCHAR(255) NOT NULL, artiste VARCHAR(255) NOT NULL, image VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE favoris (id_favoris INT AUTO_INCREMENT NOT NULL, id_user INT NOT NULL, id_produit INT NOT NULL, date DATETIME NOT NULL, INDEX IDX_8933C4326B3CA4B (id_user), INDEX IDX_8933C432F7384557 (id_produit), PRIMARY KEY(id_favoris)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE feedback (id INT AUTO_INCREMENT NOT NULL, reclamation_id INT NOT NULL, utilisateur_id INT NOT NULL, note INT NOT NULL, commentaire LONGTEXT NOT NULL, date_feedback DATE NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE promotion (id INT AUTO_INCREMENT NOT NULL, produit_id INT NOT NULL, code VARCHAR(255) NOT NULL, pourcentage INT NOT NULL, expiration DATE NOT NULL, INDEX IDX_C11D7DD1F347EFB (produit_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE reclamation (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, date_creation DATE NOT NULL, statut VARCHAR(255) NOT NULL, priorite VARCHAR(255) NOT NULL, user_id INT NOT NULL, answer VARCHAR(500) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE ticket (id INT AUTO_INCREMENT NOT NULL, evenement_id INT NOT NULL, utilisateur_id INT DEFAULT NULL, total DOUBLE PRECISION NOT NULL, qr_code VARCHAR(20000) DEFAULT NULL, methode_paiement VARCHAR(255) NOT NULL, quantite INT NOT NULL, INDEX IDX_97A0ADA3FD02F13 (evenement_id), INDEX IDX_97A0ADA3FB88E14F (utilisateur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE album_image ADD CONSTRAINT FK_B3854E79F347EFB FOREIGN KEY (produit_id) REFERENCES produit (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE favoris ADD CONSTRAINT FK_8933C4326B3CA4B FOREIGN KEY (id_user) REFERENCES user (id_user) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE favoris ADD CONSTRAINT FK_8933C432F7384557 FOREIGN KEY (id_produit) REFERENCES produit (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE promotion ADD CONSTRAINT FK_C11D7DD1F347EFB FOREIGN KEY (produit_id) REFERENCES produit (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA3FD02F13 FOREIGN KEY (evenement_id) REFERENCES evenement (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA3FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES user (id_user)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commande ADD user_id INT DEFAULT NULL, CHANGE code_postal code_postal VARCHAR(255) NOT NULL, CHANGE products_summary products_summary LONGTEXT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67D2FBB81F FOREIGN KEY (id_panier) REFERENCES panier (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id_user)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_6EEAA67D2FBB81F ON commande (id_panier)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_6EEAA67DA76ED395 ON commande (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user CHANGE id_user id_user INT AUTO_INCREMENT NOT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE album_image DROP FOREIGN KEY FK_B3854E79F347EFB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE favoris DROP FOREIGN KEY FK_8933C4326B3CA4B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE favoris DROP FOREIGN KEY FK_8933C432F7384557
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE promotion DROP FOREIGN KEY FK_C11D7DD1F347EFB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ticket DROP FOREIGN KEY FK_97A0ADA3FD02F13
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ticket DROP FOREIGN KEY FK_97A0ADA3FB88E14F
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE album_image
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE evenement
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE favoris
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE feedback
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE promotion
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE reclamation
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE ticket
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67D2FBB81F
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67DA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_6EEAA67D2FBB81F ON commande
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_6EEAA67DA76ED395 ON commande
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commande DROP user_id, CHANGE code_postal code_postal INT NOT NULL, CHANGE products_summary products_summary TEXT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user CHANGE id_user id_user INT NOT NULL
        SQL);
    }
}
