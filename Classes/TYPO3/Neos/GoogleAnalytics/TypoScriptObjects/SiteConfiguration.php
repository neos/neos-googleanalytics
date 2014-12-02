<?php
namespace TYPO3\Neos\GoogleAnalytics\TypoScriptObjects;

/*                                                                            *
 * This script belongs to the TYPO3 Flow package "TYPO3.Neos.GoogleAnalytics" *
 *                                                                            *
 * It is free software; you can redistribute it and/or modify it under        *
 * the terms of the GNU General Public License, either version 3 of the       *
 * License, or (at your option) any later version.                            *
 *                                                                            *
 * The TYPO3 project - inspiring people to share!                             *
 *                                                                            */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Neos\GoogleAnalytics\Domain\Repository\SiteConfigurationRepository;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;

class SiteConfiguration extends \TYPO3\TypoScript\TypoScriptObjects\AbstractTypoScriptObject {

	/**
	 * @Flow\Inject
	 * @var SiteConfigurationRepository
	 */
	protected $siteConfigurationRepository;

	/**
	 * @return NodeInterface
	 */
	protected function getNode() {
		return $this->tsValue('node');
	}

	/**
	 * Find a SiteConfiguration entity for the current site
	 *
	 * @return \TYPO3\Neos\GoogleAnalytics\Domain\Model\SiteConfiguration
	 */
	public function evaluate() {
		$node = $this->getNode();
		if ($node instanceof NodeInterface) {
			$contentContext = $node->getContext();
			if ($contentContext instanceof \TYPO3\Neos\Domain\Service\ContentContext) {
				$site = $contentContext->getCurrentSite();
				$siteConfiguration = $this->siteConfigurationRepository->findOneBySite($site);
				return $siteConfiguration;
			}
		}
		return NULL;
	}

}