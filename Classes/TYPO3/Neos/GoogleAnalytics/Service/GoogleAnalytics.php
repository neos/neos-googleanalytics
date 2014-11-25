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
use TYPO3\Neos\GoogleAnalytics\Exception\AuthenticationRequiredException;

/**
 * Extend the base Google Analytics API service
 *
 * @Flow\Scope("singleton")
 */
class GoogleAnalytics extends \Google_Service_Analytics {

	/**
	 * Require an authenticated Google Analytics service
	 *
	 * @return GoogleAnalytics The current instance for chaining
	 * @throws AuthenticationRequiredException
	 */
	public function requireAuthentication() {
		if ((string)$this->getClient()->getAccessToken() === '') {
			throw new AuthenticationRequiredException('No access token', 1415783205);
		}
		return $this;
	}

}