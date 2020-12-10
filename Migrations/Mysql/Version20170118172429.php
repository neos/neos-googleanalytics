<?php
namespace Neos\Flow\Persistence\Doctrine\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170118172429 extends AbstractMigration
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
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on "mysql".');

        $this->addSql('RENAME TABLE typo3_neos_googleanalytics_domain_model_siteconfiguration TO neos_googleanalytics_domain_model_siteconfiguration');

        // Renaming of indexes is only possible with MySQL version 5.7+
        if ($this->connection->getDatabasePlatform() instanceof MySQL57Platform) {
            $this->addSql('ALTER TABLE neos_googleanalytics_domain_model_siteconfiguration RENAME INDEX FK_D675F674694309E4 TO FK_DACC5FD9694309E4');
        } else {
            $this->addSql('ALTER TABLE neos_googleanalytics_domain_model_siteconfiguration DROP FOREIGN KEY FK_D675F674694309E4');
            $this->addSql('ALTER TABLE neos_googleanalytics_domain_model_siteconfiguration ADD CONSTRAINT FK_DACC5FD9694309E4 FOREIGN KEY (site) REFERENCES neos_neos_domain_model_site (persistence_object_identifier) ON DELETE CASCADE');
        }
    }

    /**
     * @param Schema $schema
     * @return void
     */
    public function down(Schema $schema): void 
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on "mysql".');

        $this->addSql('RENAME TABLE neos_googleanalytics_domain_model_siteconfiguration TO typo3_neos_googleanalytics_domain_model_siteconfiguration');

        // Renaming of indexes is only possible with MySQL version 5.7+
        if ($this->connection->getDatabasePlatform() instanceof MySQL57Platform) {
            $this->addSql('ALTER TABLE typo3_neos_googleanalytics_domain_model_siteconfiguration RENAME INDEX FK_DACC5FD9694309E4 TO FK_D675F674694309E4');
        } else {
            $this->addSql('ALTER TABLE typo3_neos_googleanalytics_domain_model_siteconfiguration DROP FOREIGN KEY FK_DACC5FD9694309E4');
            $this->addSql('ALTER TABLE typo3_neos_googleanalytics_domain_model_siteconfiguration ADD CONSTRAINT FK_D675F674694309E4 FOREIGN KEY (site) REFERENCES neos_neos_domain_model_site (persistence_object_identifier) ON DELETE CASCADE');
        }
    }
}
