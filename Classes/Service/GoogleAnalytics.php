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

use Google_Client;
use Google_Service_Analytics;
use Neos\Flow\Annotations as Flow;
use Neos\GoogleAnalytics\Exception\AuthenticationRequiredException;

/**
 * Extend the base Google Analytics API service
 *
 * @Flow\Scope("singleton")
 */
class GoogleAnalytics extends Google_Service_Analytics
{
    /**
     * @inheritdoc
     */
    public function __construct(Google_Client $client)
    {
        parent::__construct($client);

        $client->addScope(Google_Service_Analytics::ANALYTICS_READONLY);
    }

    /**
     * Require an authenticated Google Analytics service
     *
     * @return GoogleAnalytics The current instance for chaining
     * @throws AuthenticationRequiredException
     */
    public function requireAuthentication(): GoogleAnalytics
    {
        return $this;
    }
}
