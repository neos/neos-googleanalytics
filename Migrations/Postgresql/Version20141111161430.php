<?php
namespace Neos\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
	Doctrine\DBAL\Schema\Schema;

/**
 * Create a site configuration table
 */
class Version20141111161430 extends AbstractMigration {

	/**
	 * @param Schema $schema
	 * @return void
	 */
	public function up(Schema $schema) {
		$this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql");

		$this->addSql("CREATE TABLE typo3_neos_googleanalytics_domain_model_siteconfiguration (persistence_object_identifier VARCHAR(40) NOT NULL, site VARCHAR(40) DEFAULT NULL, profileid VARCHAR(255) NOT NULL, PRIMARY KEY(persistence_object_identifier))");
		$this->addSql("CREATE INDEX IDX_D675F674694309E4 ON typo3_neos_googleanalytics_domain_model_siteconfiguration (site)");
		$this->addSql("ALTER TABLE typo3_neos_googleanalytics_domain_model_siteconfiguration ADD CONSTRAINT FK_D675F674694309E4 FOREIGN KEY (site) REFERENCES typo3_neos_domain_model_site (persistence_object_identifier) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
	}

	/**
	 * @param Schema $schema
	 * @return void
	 */
	public function down(Schema $schema) {
		$this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql");

		$this->addSql("DROP TABLE typo3_neos_googleanalytics_domain_model_siteconfiguration");
	}
}