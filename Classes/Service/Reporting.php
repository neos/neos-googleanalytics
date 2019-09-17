<?php
declare(strict_types=1);

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

use Neos\ContentRepository\Domain\Service\ContextFactoryInterface;
use Neos\Flow\Annotations as Flow;
use GuzzleHttp\Psr7\Uri;
use Neos\Flow\Mvc\Controller\ControllerContext;
use Neos\Flow\Mvc\Routing\Exception\MissingActionNameException;
use Neos\Flow\Property\Exception as PropertyException;
use Neos\Flow\Security\Exception as SecurityException;
use Neos\GoogleAnalytics\Exception\AuthenticationRequiredException;
use Neos\Neos\Domain\Service\ContentContext;
use Neos\GoogleAnalytics\Domain\Dto\DataResult;
use Neos\GoogleAnalytics\Exception\AnalyticsNotAvailableException;
use Neos\GoogleAnalytics\Exception\MissingConfigurationException;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Neos\Exception as NeosException;
use Neos\Neos\Service\LinkingService;
use Neos\Utility\Arrays;

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
     * @var LinkingService
     */
    protected $linkingService;

    /**
     * @Flow\Inject
     * @var ContextFactoryInterface
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
     * @Flow\InjectConfiguration(path="default", package="Neos.GoogleAnalytics")
     * @var array
     */
    protected $defaultSettings;

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
     * @throws AnalyticsNotAvailableException
     * @throws AuthenticationRequiredException
     * @throws MissingActionNameException
     * @throws MissingConfigurationException
     * @throws NeosException
     * @throws PropertyException
     * @throws SecurityException
     */
    public function getNodeStat(NodeInterface $node, ControllerContext $controllerContext, string $statIdentifier, \DateTime $startDate, \DateTime $endDate): DataResult
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
        $hostname = !empty($siteConfiguration['overrideHostname']) ? $siteConfiguration['overrideHostname'] : $nodeUri->getHost();
        $filters = 'ga:pagePath==' . $nodeUri->getPath() . ';ga:hostname==' . $hostname;
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
            'ga:' . $siteConfiguration['profileId'],
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
     * @param NodeInterface $node
     * @return array
     * @throws MissingConfigurationException If no site configuration was found, or the profile was not assigned
     */
    protected function getSiteConfigurationByNode(NodeInterface $node): array
    {
        $context = $node->getContext();
        if (!$context instanceof ContentContext) {
            throw new \InvalidArgumentException(sprintf('Expected a ContentContext instance in the given node, got %s', get_class($context)), 1415722633);
        }
        $site = $context->getCurrentSite();
        if (array_key_exists($site->getNodeName(), $this->sitesSettings)) {
            $siteConfiguration = Arrays::arrayMergeRecursiveOverrule($this->defaultSettings, $this->sitesSettings[$site->getNodeName()]);

            if (array_key_exists('profileId', $siteConfiguration) && !empty($siteConfiguration['profileId'])) {
                return $siteConfiguration;
            }
        }
        throw new MissingConfigurationException('No profile configured for site ' . $site->getName(), 1415806282);
    }

    /**
     * Resolve an URI for the given node in the live workspace (this is where analytics usually are collected)
     *
     * @param NodeInterface $node
     * @param ControllerContext $controllerContext
     * @return Uri
     * @throws AnalyticsNotAvailableException If the node was not yet published and no live workspace URI can be resolved
     * @throws MissingActionNameException
     * @throws PropertyException
     * @throws SecurityException
     * @throws NeosException
     */
    protected function getLiveNodeUri(NodeInterface $node, ControllerContext $controllerContext): Uri
    {
        $contextProperties = $node->getContext()->getProperties();
        $contextProperties['workspaceName'] = 'live';
        $liveContext = $this->contextFactory->create($contextProperties);
        $liveNode = $liveContext->getNodeByIdentifier($node->getIdentifier());

        if ($liveNode === null) {
            throw new AnalyticsNotAvailableException('Analytics are only available on a published node', 1417450159);
        }

        $nodeUriString = $this->linkingService->createNodeUri($controllerContext, $liveNode, null, 'html', true);
        $nodeUri = new Uri($nodeUriString);

        return $nodeUri;
    }
}
