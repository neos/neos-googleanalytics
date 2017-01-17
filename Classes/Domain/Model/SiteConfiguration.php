<?php
namespace TYPO3\Neos\GoogleAnalytics\Domain\Model;

/*
 * This file is part of the TYPO3.Neos.GoogleAnalytics package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Doctrine\ORM\Mapping as ORM;
use TYPO3\Flow\Annotations as Flow;

/**
 * @Flow\Entity
 */
class SiteConfiguration
{
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
    public function getProfileId()
    {
        return $this->profileId;
    }

    /**
     * @param string $profileId
     * @return void
     */
    public function setProfileId($profileId)
    {
        $this->profileId = $profileId;
    }

    /**
     * @return \TYPO3\Neos\Domain\Model\Site
     */
    public function getSite()
    {
        return $this->site;
    }

    /**
     * @param \TYPO3\Neos\Domain\Model\Site $site
     * @return void
     */
    public function setSite($site)
    {
        $this->site = $site;
    }

    /**
     * @return string
     */
    public function getTrackingId()
    {
        return $this->trackingId;
    }

    /**
     * @param string $trackingId
     * @return void
     */
    public function setTrackingId($trackingId)
    {
        $this->trackingId = $trackingId;
    }
}
