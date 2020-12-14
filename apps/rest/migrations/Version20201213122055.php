<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201213122055 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
		$this->addSql('DROP TABLE users');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, first_name VARCHAR(50) NOT NULL, last_name VARCHAR(50) NOT NULL, nick_name VARCHAR(100) NOT NULL, password VARCHAR(255) NOT NULL, status TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql("INSERT INTO users VALUES (1,'Cus','Tomer1','customer1','45beaf34b5d6246a8ded861cd1a7023fb6828d22',1)");
        $this->addSql("INSERT INTO users VALUES (2,'Cus','Tomer2','customer2','248ad076e0407980960e25d0dbf29c88621e41a5',1)");
        $this->addSql("INSERT INTO users VALUES (3,'Cus','Tomer3','customer3','421022409be26272d59a72935184ae20f4d57206',1)");
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE users');
    }
}
