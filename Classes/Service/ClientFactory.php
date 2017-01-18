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
use Neos\GoogleAnalytics\Exception\MissingConfigurationException;

/**
 * Factory for GoogleAnalytics API client
 */
class ClientFactory
{
    /**
     * @Flow\Inject
     * @var TokenStorage
     */
    protected $tokenStorage;

    /**
     * @Flow\InjectConfiguration(path="authentication", package="Neos.GoogleAnalytics")
     * @var array
     */
    protected $authenticationSettings;

    /**
     * @throws MissingConfigurationException
     * @return \Google_Client
     */
    public function create()
    {
        $client = new \Google_Client();

        $requiredAuthenticationSettings = [
            'applicationName',
            'clientId',
            'clientSecret',
            'developerKey'
        ];
        foreach ($requiredAuthenticationSettings as $key) {
            if (!isset($this->authenticationSettings[$key])) {
                throw new MissingConfigurationException(sprintf('Missing setting "Neos.GoogleAnalytics.authentication.%s"', $key), 1415796352);
            }
        }

        $client->setApplicationName($this->authenticationSettings['applicationName']);
        $client->setClientId($this->authenticationSettings['clientId']);
        $client->setClientSecret($this->authenticationSettings['clientSecret']);

        $client->setDeveloperKey($this->authenticationSettings['developerKey']);
        $client->setScopes(['https://www.googleapis.com/auth/analytics.readonly']);
        $client->setAccessType('offline');

        $accessToken = $this->tokenStorage->getAccessToken();
        if ($accessToken !== null) {
            $client->setAccessToken($accessToken);

            if ($client->isAccessTokenExpired()) {
                $refreshToken = $this->tokenStorage->getRefreshToken();
                $client->refreshToken($refreshToken);
            }
        }

        return $client;
    }
}
