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

/**
 * @Flow\Scope("singleton")
 */
class TokenStorage {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Cache\Frontend\StringFrontend
	 */
	protected $cache;

	/**
	 * @param string $accessToken
	 * @return void
	 */
	public function storeAccessToken($accessToken) {
		$this->cache->set('AccessToken', $accessToken);
	}

	/**
	 * @param string $refreshToken
	 * @return void
	 */
	public function storeRefreshToken($refreshToken) {
		$this->cache->set('RefreshToken', $refreshToken);
	}

	/**
	 * @return string
	 */
	public function getAccessToken() {
		$accessToken = $this->cache->get('AccessToken');
		if ($accessToken === FALSE) {
			return NULL;
		}

		return $accessToken;
	}

	/**
	 * @return string
	 */
	public function getRefreshToken() {
		$accessToken = $this->cache->get('RefreshToken');
		if ($accessToken === FALSE) {
			return NULL;
		}

		return $accessToken;
	}

	/**
	 * Remove existing tokens
	 *
	 * @return void
	 */
	public function removeTokens() {
		$this->cache->remove('AccessToken');
		$this->cache->remove('RefreshToken');
	}
}