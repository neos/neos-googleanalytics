<?php
namespace Neos\Flow\Persistence\Doctrine\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;


class Version20190725070956 extends AbstractMigration
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
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on "postgresql".');
        
        $this->addSql('DROP TABLE neos_googleanalytics_domain_model_siteconfiguration');
    }

    /**
     * @param Schema $schema
     * @return void
     */
    public function down(Schema $schema): void 
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on "postgresql".');
        
        $this->addSql('CREATE TABLE neos_googleanalytics_domain_model_siteconfiguration (persistence_object_identifier VARCHAR(40) NOT NULL, site VARCHAR(40) DEFAULT NULL, profileid VARCHAR(255) NOT NULL, trackingid VARCHAR(255) NOT NULL, PRIMARY KEY(persistence_object_identifier))');
        $this->addSql('CREATE INDEX idx_dacc5fd9694309e4 ON neos_googleanalytics_domain_model_siteconfiguration (site)');
        $this->addSql('ALTER TABLE neos_googleanalytics_domain_model_siteconfiguration ADD CONSTRAINT fk_d675f674694309e4 FOREIGN KEY (site) REFERENCES neos_neos_domain_model_site (persistence_object_identifier) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
