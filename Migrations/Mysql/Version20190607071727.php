<?php
namespace Neos\Flow\Persistence\Doctrine\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20190607071727 extends AbstractMigration
{

    /**
     * @return string
     */
    public function getDescription(): string 
    {
        return 'Remove deprecated site configuration entity';
    }

    /**
     * @param Schema $schema
     * @return void
     */
    public function up(Schema $schema): void 
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on "mysql".');

        $this->addSql('DROP TABLE neos_googleanalytics_domain_model_siteconfiguration');
    }

    /**
     * @param Schema $schema
     * @return void
     */
    public function down(Schema $schema): void 
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on "mysql".');

        $this->addSql('CREATE TABLE neos_googleanalytics_domain_model_siteconfiguration (persistence_object_identifier VARCHAR(40) NOT NULL COLLATE utf8_unicode_ci, site VARCHAR(40) DEFAULT NULL COLLATE utf8_unicode_ci, profileid VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, trackingid VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, INDEX IDX_D675F674694309E4 (site), PRIMARY KEY(persistence_object_identifier)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE neos_googleanalytics_domain_model_siteconfiguration ADD CONSTRAINT FK_DACC5FD9694309E4 FOREIGN KEY (site) REFERENCES neos_neos_domain_model_site (persistence_object_identifier) ON DELETE CASCADE');
    }
}
