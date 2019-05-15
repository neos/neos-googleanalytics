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

use Neos\Cache\Exception as CacheException;
use Neos\Cache\Exception\InvalidDataException;
use Neos\Cache\Frontend\StringFrontend;
use Neos\Flow\Annotations as Flow;

/**
 * @Flow\Scope("singleton")
 */
class TokenStorage
{
    /**
     * @Flow\Inject
     * @var StringFrontend
     */
    protected $cache;

    /**
     * @param string $accessToken
     * @return void
     * @throws CacheException
     * @throws InvalidDataException
     */
    public function storeAccessToken($accessToken)
    {
        $this->cache->set('AccessToken', $accessToken);
    }

    /**
     * @param string $refreshToken
     * @return void
     * @throws CacheException
     * @throws InvalidDataException
     */
    public function storeRefreshToken($refreshToken)
    {
        $this->cache->set('RefreshToken', $refreshToken);
    }

    /**
     * @return string
     */
    public function getAccessToken(): string
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
    public function getRefreshToken(): string
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
