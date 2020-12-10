<?php
namespace Neos\Flow\Persistence\Doctrine\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add trackingId to SiteConfiguration
 */
class Version20141202134559 extends AbstractMigration
{
    /**
     * @return string
     */
    public function getDescription(): string 
    {
        return 'Add trackingId to SiteConfiguration.';
    }

    /**
     * @param Schema $schema
     * @return void
     */
    public function up(Schema $schema): void 
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on "postgresql".');

        $this->addSql('ALTER TABLE typo3_neos_googleanalytics_domain_model_siteconfiguration ADD trackingid VARCHAR(255) NOT NULL');
    }

    /**
     * @param Schema $schema
     * @return void
     */
    public function down(Schema $schema): void 
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on "postgresql".');

        $this->addSql('ALTER TABLE typo3_neos_googleanalytics_domain_model_siteconfiguration DROP trackingid');
    }
}
