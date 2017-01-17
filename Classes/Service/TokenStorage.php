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

/**
 * @Flow\Scope("singleton")
 */
class TokenStorage
{
    /**
     * @Flow\Inject
     * @var \TYPO3\Flow\Cache\Frontend\StringFrontend
     */
    protected $cache;

    /**
     * @param string $accessToken
     * @return void
     */
    public function storeAccessToken($accessToken)
    {
        $this->cache->set('AccessToken', $accessToken);
    }

    /**
     * @param string $refreshToken
     * @return void
     */
    public function storeRefreshToken($refreshToken)
    {
        $this->cache->set('RefreshToken', $refreshToken);
    }

    /**
     * @return string
     */
    public function getAccessToken()
    {
        $accessToken = $this->cache->get('AccessToken');
        if ($accessToken === false) {
            return null;
        }

        return $accessToken;
    }

    /**
     * @return string
     */
    public function getRefreshToken()
    {
        $accessToken = $this->cache->get('RefreshToken');
        if ($accessToken === false) {
            return null;
        }

        return $accessToken;
    }

    /**
     * Remove existing tokens
     *
     * @return void
     */
    public function removeTokens()
    {
        $this->cache->remove('AccessToken');
        $this->cache->remove('RefreshToken');
    }
}
