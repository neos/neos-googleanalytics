<?php
namespace TYPO3\Neos\GoogleAnalytics\TypoScriptObjects;

/*
 * This file is part of the TYPO3.Neos.GoogleAnalytics package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Neos\GoogleAnalytics\Domain\Repository\SiteConfigurationRepository;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;

class SiteConfiguration extends \TYPO3\TypoScript\TypoScriptObjects\AbstractTypoScriptObject
{
    /**
     * @Flow\Inject
     * @var SiteConfigurationRepository
     */
    protected $siteConfigurationRepository;

    /**
     * @return NodeInterface
     */
    protected function getNode()
    {
        return $this->tsValue('node');
    }

    /**
     * Find a SiteConfiguration entity for the current site
     *
     * @return \TYPO3\Neos\GoogleAnalytics\Domain\Model\SiteConfiguration
     */
    public function evaluate()
    {
        $node = $this->getNode();
        if ($node instanceof NodeInterface) {
            $contentContext = $node->getContext();
            if ($contentContext instanceof \TYPO3\Neos\Domain\Service\ContentContext) {
                $site = $contentContext->getCurrentSite();
                $siteConfiguration = $this->siteConfigurationRepository->findOneBySite($site);

                return $siteConfiguration;
            }
        }

        return null;
    }
}
