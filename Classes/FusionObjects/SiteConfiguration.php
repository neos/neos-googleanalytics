<?php
namespace Neos\GoogleAnalytics\FusionObjects;

/*
 * This file is part of the Neos.GoogleAnalytics package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;
use Neos\GoogleAnalytics\Domain\Repository\SiteConfigurationRepository;
use Neos\ContentRepository\Domain\Model\NodeInterface;

class SiteConfiguration extends \Neos\Fusion\FusionObjects\AbstractFusionObject
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
     * @return \Neos\GoogleAnalytics\Domain\Model\SiteConfiguration
     */
    public function evaluate()
    {
        $node = $this->getNode();
        if ($node instanceof NodeInterface) {
            $contentContext = $node->getContext();
            if ($contentContext instanceof \Neos\Neos\Domain\Service\ContentContext) {
                $site = $contentContext->getCurrentSite();
                $siteConfiguration = $this->siteConfigurationRepository->findOneBySite($site);

                return $siteConfiguration;
            }
        }

        return null;
    }
}
