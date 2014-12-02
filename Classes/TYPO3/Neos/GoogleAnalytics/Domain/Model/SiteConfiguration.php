<?php
namespace TYPO3\Neos\GoogleAnalytics\Domain\Model;

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
use Doctrine\ORM\Mapping as ORM;

/**
 * @Flow\Entity
 */
class SiteConfiguration {

	/**
	 * @ORM\ManyToOne
	 * @ORM\JoinColumn(onDelete="CASCADE")
	 * @var \TYPO3\Neos\Domain\Model\Site
	 */
	protected $site;

	/**
	 * @var string
	 */
	protected $profileId;

	/**
	 * @var string
	 */
	protected $trackingId;

	/**
	 * @return string
	 */
	public function getProfileId() {
		return $this->profileId;
	}

	/**
	 * @param string $profileId
	 * @return void
	 */
	public function setProfileId($profileId) {
		$this->profileId = $profileId;
	}

	/**
	 * @return \TYPO3\Neos\Domain\Model\Site
	 */
	public function getSite() {
		return $this->site;
	}

	/**
	 * @param \TYPO3\Neos\Domain\Model\Site $site
	 * @return void
	 */
	public function setSite($site) {
		$this->site = $site;
	}

	/**
	 * @return string
	 */
	public function getTrackingId() {
		return $this->trackingId;
	}

	/**
	 * @param string $trackingId
	 * @return void
	 */
	public function setTrackingId($trackingId) {
		$this->trackingId = $trackingId;
	}

}