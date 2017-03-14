<?php
namespace Neos\GoogleAnalytics\Service;

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
use Neos\Flow\Mvc\Controller\ControllerContext;
use Neos\Neos\Domain\Service\ContentContext;
use Neos\GoogleAnalytics\Domain\Dto\DataResult;
use Neos\GoogleAnalytics\Domain\Model\SiteConfiguration;
use Neos\GoogleAnalytics\Exception\AnalyticsNotAvailableException;
use Neos\GoogleAnalytics\Exception\MissingConfigurationException;
use Neos\ContentRepository\Domain\Model\NodeInterface;

/**
 * @Flow\Scope("singleton")
 */
class Reporting
{
    /**
     * @Flow\Inject
     * @var GoogleAnalytics
     */
    protected $analytics;

    /**
     * @Flow\Inject
     * @var \Neos\GoogleAnalytics\Domain\Repository\SiteConfigurationRepository
     */
    protected $siteConfigurationRepository;

    /**
     * @Flow\Inject
     * @var \Neos\Neos\Service\LinkingService
     */
    protected $linkingService;

    /**
     * @Flow\Inject
     * @var \Neos\ContentRepository\Domain\Service\ContextFactoryInterface
     */
    protected $contextFactory;

    /**
     * @Flow\InjectConfiguration(path="stats", package="Neos.GoogleAnalytics")
     * @var array
     */
    protected $statsSettings;

    /**
     * @Flow\InjectConfiguration(path="sites", package="Neos.GoogleAnalytics")
     * @var array
     */
    protected $sitesSettings;

    /**
     * Get metrics and dimension values for a configured stat
     *
     * TODO Catch "(403) Access Not Configured" (e.g. IP does not match)
     *
     * @param NodeInterface $node
     * @param ControllerContext $controllerContext
     * @param string $statIdentifier
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @return DataResult
     * @throws MissingConfigurationException
     * @throws AnalyticsNotAvailableException
     */
    public function getNodeStat(NodeInterface $node, ControllerContext $controllerContext, $statIdentifier, \DateTime $startDate, \DateTime $endDate)
    {
        $this->analytics->requireAuthentication();
        if (!isset($this->statsSettings[$statIdentifier])) {
            throw new \InvalidArgumentException(sprintf('Unknown stat identifier "%s"', $statIdentifier), 1416917316);
        }
        $statConfiguration = $this->statsSettings[$statIdentifier];
        $siteConfiguration = $this->getSiteConfigurationByNode($node);

        $startDateFormatted = $startDate->format('Y-m-d');
        $endDateFormatted = $endDate->format('Y-m-d');

        $nodeUri = $this->getLiveNodeUri($node, $controllerContext);
        $filters = 'ga:pagePath==' . $nodeUri->getPath() . ';ga:hostname==' . $nodeUri->getHost();
        $parameters = [
            'filters' => $filters
        ];
        if (isset($statConfiguration['dimensions'])) {
            $parameters['dimensions'] = $statConfiguration['dimensions'];
        }
        if (isset($statConfiguration['sort'])) {
            $parameters['sort'] = $statConfiguration['sort'];
        }
        if (isset($statConfiguration['max-results'])) {
            $parameters['max-results'] = $statConfiguration['max-results'];
        }
        $gaResult = $this->analytics->data_ga->get(
            'ga:' . $siteConfiguration->getProfileId(),
            $startDateFormatted,
            $endDateFormatted,
            $statConfiguration['metrics'],
            $parameters
        );

        return new DataResult($gaResult);
    }

    /**
     * Get a site configuration (which has a Google Analytics profile id) for the given node
     *
     * This will first look for a SiteConfiguration entity and then fall back to site specific settings.
     *
     * @param NodeInterface $node
     * @return SiteConfiguration
     * @throws MissingConfigurationException If no site configuration was found, or the profile was not assigned
     */
    protected function getSiteConfigurationByNode(NodeInterface $node)
    {
        $context = $node->getContext();
        if (!$context instanceof ContentContext) {
            throw new \InvalidArgumentException(sprintf('Expected a ContentContext instance in the given node, got %s', get_class($context)), 1415722633);
        }
        $site = $context->getCurrentSite();
        $siteConfiguration = $this->siteConfigurationRepository->findOneBySite($site);

        if ($siteConfiguration instanceof SiteConfiguration && $siteConfiguration->getProfileId() !== '') {
            return $siteConfiguration;
        } else {
            if (isset($this->sitesSettings[$site->getNodeName()]['profileId']) && (string)$this->sitesSettings[$site->getNodeName()]['profileId'] !== '') {
                $siteConfiguration = new SiteConfiguration();
                $siteConfiguration->setProfileId($this->sitesSettings[$site->getNodeName()]['profileId']);

                return $siteConfiguration;
            }
            throw new MissingConfigurationException('No profile configured for site', 1415806282);
        }
    }

    /**
     * Resolve an URI for the given node in the live workspace (this is where analytics usually are collected)
     *
     * @param NodeInterface $node
     * @param ControllerContext $controllerContext
     * @return \Neos\Flow\Http\Uri
     * @throws AnalyticsNotAvailableException If the node was not yet published and no live workspace URI can be resolved
     */
    protected function getLiveNodeUri(NodeInterface $node, ControllerContext $controllerContext)
    {
        $contextProperties = $node->getContext()->getProperties();
        $contextProperties['workspaceName'] = 'live';
        $liveContext = $this->contextFactory->create($contextProperties);
        $liveNode = $liveContext->getNodeByIdentifier($node->getIdentifier());

        if ($liveNode === null) {
            throw new AnalyticsNotAvailableException('Analytics are only available on a published node', 1417450159);
        }

        $nodeUriString = $this->linkingService->createNodeUri($controllerContext, $liveNode, null, 'html', true);
        $nodeUri = new \Neos\Flow\Http\Uri($nodeUriString);

        return $nodeUri;
    }
}
