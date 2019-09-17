<?php
declare(strict_types=1);

namespace Neos\GoogleAnalytics\Eel\Helper;

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
use Neos\ContentRepository\Domain\NodeAggregate\NodeName;
use Neos\ContentRepository\Domain\Projection\Content\NodeInterface;
use Neos\Eel\ProtectedContextAwareInterface;
use Neos\Utility\Arrays;

/**
 * Class TrackingConfiguration
 */
class TrackingConfiguration implements ProtectedContextAwareInterface
{
    /**
     * @Flow\InjectConfiguration(path="default", package="Neos.GoogleAnalytics")
     * @var array
     */
    protected $defaultSettings;

    /**
     * @Flow\InjectConfiguration(path="sites", package="Neos.GoogleAnalytics")
     * @var array
     */
    protected $sitesSettings;

    /**
     * Gets tracking settings
     *
     * If no site node is provided, this will get the default settings.
     * If no path is provided, this will get all settings
     *
     * @param NodeInterface|null $site the site node for which to get settings
     * @param string|array|null $path the settings path
     * @return mixed
     */
    public function setting(?NodeInterface $site = null, $path = null)
    {
        $settings = $this->defaultSettings;

        $nodeName = null;
        if ($site instanceof NodeInterface) {
            $nodeName = $site->getNodeName();
        }

        if ($nodeName instanceof NodeName && isset($this->sitesSettings[(string)$nodeName])) {
            $settings = Arrays::arrayMergeRecursiveOverrule($this->defaultSettings, $this->sitesSettings[(string)$nodeName]);
        }

        if ($path === null) {
            return $settings;
        }

        return Arrays::getValueByPath($settings, $path);
    }

    /**
     * @param string $methodName
     * @return bool
     */
    public function allowsCallOfMethod($methodName): bool
    {
        return true;
    }
}
