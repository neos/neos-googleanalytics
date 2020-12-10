<?php
namespace Neos\Flow\Persistence\Doctrine\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170118172430 extends AbstractMigration
{
    /**
     * @return string
     */
    public function getDescription(): string 
    {
        return 'Adjust table names to new package key.';
    }

    /**
     * @param Schema $schema
     * @return void
     */
    public function up(Schema $schema): void 
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on "postgresql".');

        $this->addSql('ALTER TABLE typo3_neos_googleanalytics_domain_model_siteconfiguration RENAME TO neos_googleanalytics_domain_model_siteconfiguration');
        $this->addSql('ALTER INDEX idx_d675f674694309e4 RENAME TO idx_dacc5fd9694309e4');
    }

    /**
     * @param Schema $schema
     * @return void
     */
    public function down(Schema $schema): void 
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on "postgresql".');

        $this->addSql('ALTER TABLE neos_googleanalytics_domain_model_siteconfiguration RENAME TO typo3_neos_googleanalytics_domain_model_siteconfiguration');
        $this->addSql('ALTER INDEX idx_dacc5fd9694309e4 RENAME TO idx_d675f674694309e4');
    }
}
