<?php
namespace Neos\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
	Doctrine\DBAL\Schema\Schema;

/**
 * Create a site configuration table
 */
class Version20141111161429 extends AbstractMigration {

	/**
	 * @param Schema $schema
	 * @return void
	 */
	public function up(Schema $schema) {
		$this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

		$this->addSql("CREATE TABLE typo3_neos_googleanalytics_domain_model_siteconfiguration (persistence_object_identifier VARCHAR(40) NOT NULL, site VARCHAR(40) DEFAULT NULL, profileid VARCHAR(255) NOT NULL, INDEX IDX_D675F674694309E4 (site), PRIMARY KEY(persistence_object_identifier)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        /**
         * We need to check what the name of the site table is. If you install Neos, run migrations, then add this package,
         * then run this package's migrations, the name will will be neos_flow_...
         * However, if you install everything in one go and run migrations then, the order will be different because this migration
         * comes before the Flow migration where the table is renamed (Version20161124185047). So we need to check which of these two
         * tables exist and set the FK relation accordingly.
         **/
        if ($this->sm->tablesExist('neos_neos_domain_model_site')) {
            // "neos_" table is there - this means flow migrations have already been run.
            $this->addSql("ALTER TABLE typo3_neos_googleanalytics_domain_model_siteconfiguration ADD CONSTRAINT FK_D675F674694309E4 FOREIGN KEY (site) REFERENCES neos_neos_domain_model_site (persistence_object_identifier) ON DELETE CASCADE");
        } else if ($this->sm->tablesExist('typo3_neos_domain_model_site')) {
            // Flow migrations have not been run fully yet, table still has the old name.
            $this->addSql("ALTER TABLE typo3_neos_googleanalytics_domain_model_siteconfiguration ADD CONSTRAINT FK_D675F674694309E4 FOREIGN KEY (site) REFERENCES typo3_neos_domain_model_site (persistence_object_identifier) ON DELETE CASCADE");
        }
	}

	/**
	 * @param Schema $schema
	 * @return void
	 */
	public function down(Schema $schema) {
		$this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

		$this->addSql("DROP TABLE typo3_neos_googleanalytics_domain_model_siteconfiguration");
	}
}
