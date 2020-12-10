<?php
namespace Neos\Flow\Persistence\Doctrine\Migrations;

use Doctrine\Migrations\AbstractMigration,
	Doctrine\DBAL\Schema\Schema;

/**
 * Create a site configuration table
 */
class Version20141111161429 extends AbstractMigration {

	/**
	 * @param Schema $schema
	 * @return void
	 */
	public function up(Schema $schema): void  {
		$this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");
        /**
         * With the new version of NEOS the recommended database charset is utf8mb4 and not just utf8 anymore.
         * To make the foreign key constraint work the table/cell encoding must match the
         * encoding of typo3_neos_domain_model_site / neos_domain_model_site.
         * Solution: First check encoding of typo3_neos_domain_model_site / neos_domain_model_site,
         * then create neos_googleanalytics_domain_model_siteconfiguration accordingly.
         * AND
         * We need to check what the name of the site table is. If you install Neos, run migrations, then add this package,
         * then run this package's migrations, the name will will be neos_flow_...
         * However, if you install everything in one go and run migrations then, the order will be different because this migration
         * comes before the Flow migration where the table is renamed (Version20161124185047). So we need to check which of these two
         * tables exist and set the FK relation accordingly.
         **/

        if ($this->sm->tablesExist('neos_neos_domain_model_site')) {
            $tableName = 'neos_neos_domain_model_site';
        } elseif ($this->sm->tablesExist('typo3_neos_domain_model_site')) {
            $tableName = 'typo3_neos_domain_model_site';
        } else {
            return; // Nothing to do here
        }

        // the column name can come out of the query as upper or lower case
        // (see https://github.com/neos/neos-googleanalytics/pull/70 and
        // https://github.com/neos/neos-googleanalytics/issues/72 for details)
        // - thus the AS is added.
        $columnCharSets = $this->connection->executeQuery("SELECT character_set_name AS charsetname FROM information_schema.`COLUMNS` WHERE table_schema IN (SELECT DATABASE()) AND table_name = '${tableName}' AND column_name = 'persistence_object_identifier'")->fetch();
        $charSet = $columnCharSets['charsetname'];

        $this->addSql("CREATE TABLE typo3_neos_googleanalytics_domain_model_siteconfiguration (persistence_object_identifier VARCHAR(40) NOT NULL, site VARCHAR(40) DEFAULT NULL, profileid VARCHAR(255) NOT NULL, INDEX IDX_D675F674694309E4 (site), PRIMARY KEY(persistence_object_identifier)) DEFAULT CHARACTER SET ${charSet} COLLATE ${charSet}_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE typo3_neos_googleanalytics_domain_model_siteconfiguration ADD CONSTRAINT FK_D675F674694309E4 FOREIGN KEY (site) REFERENCES ${tableName} (persistence_object_identifier) ON DELETE CASCADE");
    }

	/**
	 * @param Schema $schema
	 * @return void
	 */
	public function down(Schema $schema): void  {
		$this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

		$this->addSql("DROP TABLE typo3_neos_googleanalytics_domain_model_siteconfiguration");
	}
}
