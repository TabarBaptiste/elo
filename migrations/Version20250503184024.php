<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250503184024 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation_prestation DROP FOREIGN KEY FK_31624619B83297E7
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation_prestation ADD CONSTRAINT FK_31624619B83297E7 FOREIGN KEY (reservation_id) REFERENCES reservation (id) ON DELETE CASCADE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation_prestation DROP FOREIGN KEY FK_31624619B83297E7
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation_prestation ADD CONSTRAINT FK_31624619B83297E7 FOREIGN KEY (reservation_id) REFERENCES reservation (id) ON UPDATE NO ACTION ON DELETE NO ACTION
        SQL);
    }
}
