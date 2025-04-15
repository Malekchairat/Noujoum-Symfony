<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250403023914 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE commande');
        $this->addSql('ALTER TABLE evenement CHANGE titre titre VARCHAR(255) NOT NULL, CHANGE image image VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE ticket DROP FOREIGN KEY FK_ticket_user');
        $this->addSql('ALTER TABLE ticket DROP FOREIGN KEY FK_ticket_evenement');
        $this->addSql('DROP INDEX FK_ticket_user ON ticket');
        $this->addSql('DROP INDEX FK_ticket_evenement ON ticket');
        $this->addSql('ALTER TABLE ticket CHANGE qr_code qr_code VARCHAR(20000) DEFAULT NULL, CHANGE id_evenement_id evenement_id INT NOT NULL, CHANGE id_utilisateur_id utilisateur_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA3FD02F13 FOREIGN KEY (evenement_id) REFERENCES evenement (id)');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA3FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_97A0ADA3FD02F13 ON ticket (evenement_id)');
        $this->addSql('CREATE INDEX IDX_97A0ADA3FB88E14F ON ticket (utilisateur_id)');
        $this->addSql('ALTER TABLE user ADD username VARCHAR(255) NOT NULL, DROP nom, DROP prenom, DROP mdp, DROP tel, DROP role, DROP image');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE commande (id INT AUTO_INCREMENT NOT NULL, id_panier INT DEFAULT NULL, rue VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, ville VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, code_postal INT NOT NULL, etat VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, montant_total INT NOT NULL, methode_paiment VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, id_user INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE evenement CHANGE titre titre VARCHAR(200) NOT NULL, CHANGE image image MEDIUMTEXT NOT NULL');
        $this->addSql('ALTER TABLE ticket DROP FOREIGN KEY FK_97A0ADA3FD02F13');
        $this->addSql('ALTER TABLE ticket DROP FOREIGN KEY FK_97A0ADA3FB88E14F');
        $this->addSql('DROP INDEX IDX_97A0ADA3FD02F13 ON ticket');
        $this->addSql('DROP INDEX IDX_97A0ADA3FB88E14F ON ticket');
        $this->addSql('ALTER TABLE ticket CHANGE qr_code qr_code MEDIUMTEXT DEFAULT NULL, CHANGE evenement_id id_evenement_id INT NOT NULL, CHANGE utilisateur_id id_utilisateur_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT FK_ticket_user FOREIGN KEY (id_utilisateur_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT FK_ticket_evenement FOREIGN KEY (id_evenement_id) REFERENCES evenement (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX FK_ticket_user ON ticket (id_utilisateur_id)');
        $this->addSql('CREATE INDEX FK_ticket_evenement ON ticket (id_evenement_id)');
        $this->addSql('ALTER TABLE user ADD prenom VARCHAR(255) NOT NULL, ADD mdp VARCHAR(255) NOT NULL, ADD tel INT NOT NULL, ADD role VARCHAR(255) NOT NULL, ADD image VARCHAR(255) NOT NULL, CHANGE username nom VARCHAR(255) NOT NULL');
    }
}
