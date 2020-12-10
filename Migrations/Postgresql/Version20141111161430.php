<?php
namespace Neos\Flow\Persistence\Doctrine\Migrations;

use Doctrine\Migrations\AbstractMigration,
	Doctrine\DBAL\Schema\Schema;

/**
 * Create a site configuration table
 */
class Version20141111161430 extends AbstractMigration {

	/**
	 * @param Schema $schema
	 * @return void
	 */
	public function up(Schema $schema): void  {
		$this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql");

		$this->addSql("CREATE TABLE typo3_neos_googleanalytics_domain_model_siteconfiguration (persistence_object_identifier VARCHAR(40) NOT NULL, site VARCHAR(40) DEFAULT NULL, profileid VARCHAR(255) NOT NULL, PRIMARY KEY(persistence_object_identifier))");
		$this->addSql("CREATE INDEX IDX_D675F674694309E4 ON typo3_neos_googleanalytics_domain_model_siteconfiguration (site)");
        /**
         * We need to check what the name of the site table is. If you install Neos, run migrations, then add this package,
         * then run this package's migrations, the name will will be neos_flow_...
         * However, if you install everything in one go and run migrations then, the order will be different because this migration
         * comes before the Flow migration where the table is renamed (Version20161124185047). So we need to check which of these two
         * tables exist and set the FK relation accordingly.
         **/
        if ($this->sm->tablesExist('neos_neos_domain_model_site')) {
            // "neos_" table is there - this means flow migrations have already been run.
            $this->addSql("ALTER TABLE typo3_neos_googleanalytics_domain_model_siteconfiguration ADD CONSTRAINT FK_D675F674694309E4 FOREIGN KEY (site) REFERENCES neos_neos_domain_model_site (persistence_object_identifier) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        } else if ($this->sm->tablesExist('typo3_neos_domain_model_site')) {
            // Flow migrations have not been run fully yet, table still has the old name.
            $this->addSql("ALTER TABLE typo3_neos_googleanalytics_domain_model_siteconfiguration ADD CONSTRAINT FK_D675F674694309E4 FOREIGN KEY (site) REFERENCES typo3_neos_domain_model_site (persistence_object_identifier) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        }
	}

	/**
	 * @param Schema $schema
	 * @return void
	 */
	public function down(Schema $schema): void  {
		$this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql");

		$this->addSql("DROP TABLE typo3_neos_googleanalytics_domain_model_siteconfiguration");
	}
}
