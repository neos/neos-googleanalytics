<?php
namespace TYPO3\Neos\GoogleAnalytics\Service;

/*                                                                            *
 * This script belongs to the TYPO3 Flow package "TYPO3.Neos.GoogleAnalytics" *
 *                                                                            *
 * It is free software; you can redistribute it and/or modify it under        *
 * the terms of the GNU General Public License, either version 3 of the       *
 * License, or (at your option) any later version.                            *
 *                                                                            *
 * The TYPO3 project - inspiring people to share!                             *
 *                                                                            */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Neos\GoogleAnalytics\Exception\MissingConfigurationException;

/**
 * Factory for GoogleAnalytics API client
 */
class ClientFactory {

	/**
	 * @Flow\Inject
	 * @var TokenStorage
	 */
	protected $tokenStorage;

	/**
	 * @Flow\Inject(setting="authentication", package="TYPO3.Neos.GoogleAnalytics")
	 * @var array
	 */
	protected $authenticationSettings;

	/**
	 * @throws MissingConfigurationException
	 * @return \Google_Client
	 */
	public function create() {
		$client = new \Google_Client();

		$requiredAuthenticationSettings = array(
			'applicationName',
			'clientId',
			'clientSecret',
			'developerKey'
		);
		foreach ($requiredAuthenticationSettings as $key) {
			if (!isset($this->authenticationSettings[$key])) {
				throw new MissingConfigurationException(sprintf('Missing setting "TYPO3.Neos.GoogleAnalytics.authentication.%s"', $key), 1415796352);
			}
		}

		$client->setApplicationName($this->authenticationSettings['applicationName']);
		$client->setClientId($this->authenticationSettings['clientId']);
		$client->setClientSecret($this->authenticationSettings['clientSecret']);

		$client->setDeveloperKey($this->authenticationSettings['developerKey']);
		$client->setScopes(array('https://www.googleapis.com/auth/analytics.readonly'));
		$client->setAccessType('offline');

		$accessToken = $this->tokenStorage->getAccessToken();
		if ($accessToken !== NULL) {
			$client->setAccessToken($accessToken);

			if ($client->isAccessTokenExpired()) {
				$refreshToken = $this->tokenStorage->getRefreshToken();
				$client->refreshToken($refreshToken);
			}
		}

		return $client;
	}
}