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
use Neos\Fusion\FusionObjects\AbstractFusionObject;
use Neos\GoogleAnalytics\Domain\Repository\SiteConfigurationRepository;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Neos\Domain\Service\ContentContext;

class SiteConfiguration extends AbstractFusionObject
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
        return $this->fusionValue('node');
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
            if ($contentContext instanceof ContentContext) {
                $site = $contentContext->getCurrentSite();
                $siteConfiguration = $this->siteConfigurationRepository->findOneBySite($site);

                return $siteConfiguration;
            }
        }

        return null;
    }
}
