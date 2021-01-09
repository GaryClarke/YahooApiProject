<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210109094004 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE stock_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE stock (
                        id INT NOT NULL, 
                        symbol VARCHAR(4) NOT NULL, 
                        currency VARCHAR(3) NOT NULL, 
                        exchange_name VARCHAR(30) NOT NULL, 
                        price DOUBLE PRECISION NOT NULL, 
                        price_change DOUBLE PRECISION DEFAULT NULL, 
                        previous_close DOUBLE PRECISION DEFAULT NULL, 
                        region VARCHAR(3) NOT NULL, 
                        short_name VARCHAR(30) DEFAULT NULL, 
                        PRIMARY KEY(id))'
        );
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE stock_id_seq CASCADE');
        $this->addSql('DROP TABLE stock');
    }
}
