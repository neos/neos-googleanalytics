<?php
namespace TYPO3\Neos\GoogleAnalytics\Service;

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
use TYPO3\Neos\GoogleAnalytics\Exception\AuthenticationRequiredException;

/**
 * Extend the base Google Analytics API service
 *
 * @Flow\Scope("singleton")
 */
class GoogleAnalytics extends \Google_Service_Analytics
{
    /**
     * Require an authenticated Google Analytics service
     *
     * @return GoogleAnalytics The current instance for chaining
     * @throws AuthenticationRequiredException
     */
    public function requireAuthentication()
    {
        if ((string)$this->getClient()->getAccessToken() === '') {
            throw new AuthenticationRequiredException('No access token', 1415783205);
        }

        return $this;
    }
}
