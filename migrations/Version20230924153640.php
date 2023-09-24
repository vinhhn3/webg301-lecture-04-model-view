<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230924153640 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `order` (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE order_books (order_id INT NOT NULL, book_id INT NOT NULL, INDEX IDX_C9855E6F8D9F6D38 (order_id), INDEX IDX_C9855E6F16A2B381 (book_id), PRIMARY KEY(order_id, book_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE order_books ADD CONSTRAINT FK_C9855E6F8D9F6D38 FOREIGN KEY (order_id) REFERENCES `order` (id)');
        $this->addSql('ALTER TABLE order_books ADD CONSTRAINT FK_C9855E6F16A2B381 FOREIGN KEY (book_id) REFERENCES book (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE order_books DROP FOREIGN KEY FK_C9855E6F8D9F6D38');
        $this->addSql('ALTER TABLE order_books DROP FOREIGN KEY FK_C9855E6F16A2B381');
        $this->addSql('DROP TABLE `order`');
        $this->addSql('DROP TABLE order_books');
    }
}
