<?php
namespace TYPO3\Neos\GoogleAnalytics\Controller;

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
use TYPO3\Neos\GoogleAnalytics\Exception\MissingConfigurationException;

/**
 * The ConfigurationController handles the configuration of the Google Analytics module,
 * including connecting to the Google API via OAuth2 and assigning profiles to sites in Neos.
 */
class ConfigurationController extends \TYPO3\Flow\Mvc\Controller\ActionController {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Neos\Domain\Repository\SiteRepository
	 */
	protected $siteRepository;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Neos\GoogleAnalytics\Domain\Repository\SiteConfigurationRepository
	 */
	protected $siteConfigurationRepository;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Neos\GoogleAnalytics\Service\TokenStorage
	 */
	protected $tokenStorage;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Neos\GoogleAnalytics\Service\GoogleAnalytics
	 */
	protected $analytics;

	/**
	 * Show a list of sites and assigned GA profiles
	 *
	 * @return void
	 */
	public function indexAction() {
		$siteConfigurations = $this->siteConfigurationRepository->findAll();

		$sites = $this->siteRepository->findAll();
		$sitesWithConfiguration = array();
		foreach ($sites as $site) {
			$item = array('site' => $site);
			foreach ($siteConfigurations as $siteConfiguration) {
				if ($siteConfiguration->getSite() === $site) {
					$item['configuration'] = $siteConfiguration;
				}
			}
			$sitesWithConfiguration[] = $item;
		}
		$this->view->assign('sitesWithConfiguration', $sitesWithConfiguration);

		$profiles = $this->getGroupedProfiles();
		$this->view->assign('groupedProfiles', $profiles);
	}

	/**
	 * Update or add site configurations
	 *
	 * @param array<\TYPO3\Neos\GoogleAnalytics\Domain\Model\SiteConfiguration> $siteConfigurations Array of site configurations
	 * @return void
	 */
	public function updateAction(array $siteConfigurations) {
		foreach ($siteConfigurations as $siteConfiguration) {
			if ($this->persistenceManager->isNewObject($siteConfiguration)) {
				$this->siteConfigurationRepository->add($siteConfiguration);
			} else {
				$this->siteConfigurationRepository->update($siteConfiguration);
			}
		}

		$this->addFlashMessage('Configuration has been updated.', 'Update', NULL, array(), 1417109043);
		$this->redirect('index');
	}

	/**
	 * @return void
	 */
	public function authenticateAction() {
		$client = $this->analytics->getClient();

		$redirectUri = $this->uriBuilder->reset()
			->setCreateAbsoluteUri(TRUE)
			->uriFor('authenticate');
		$client->setRedirectUri($this->removeUriQueryArguments($redirectUri));

		// We have to get the "code" query argument without a module prefix
		$code = $this->request->getHttpRequest()->getArgument('code');
		if (!empty($code)) {
			$client->authenticate($code);

			$this->tokenStorage->storeAccessToken($client->getAccessToken());
			$this->tokenStorage->storeRefreshToken($client->getRefreshToken());

			$indexUri = $this->uriBuilder->reset()
				->setCreateAbsoluteUri(TRUE)
				->uriFor('index');
			$this->redirectToUri($this->removeUriQueryArguments($indexUri));
		}

		// If we don't have a refresh token, require an approval prompt to receive a refresh token
		$refreshToken = $this->tokenStorage->getRefreshToken();
		if ($refreshToken === NULL) {
			$client->setApprovalPrompt('force');
		}

		$authUrl = $client->createAuthUrl();
		$this->view->assign('authUrl', $authUrl);
	}

	/**
	 * @return void
	 */
	public function errorMessageAction() {
		// TODO Add some way to re-authenticate / delete access tokens
	}

	/**
	 * Catch Google service exceptions and forward to the "apiError" action to show
	 * an error message.
	 *
	 * @return void
	 */
	protected function callActionMethod() {
		try {
			parent::callActionMethod();
		} catch (\Google_Service_Exception $exception) {
			$this->addFlashMessage('%1$s', 'Google API error', \TYPO3\Flow\Error\Message::SEVERITY_ERROR, array('message' => $exception->getMessage(), 1415797974));
			$this->forward('errorMessage');
		} catch (MissingConfigurationException $exception) {
			$this->addFlashMessage('%1$s', 'Missing configuration', \TYPO3\Flow\Error\Message::SEVERITY_ERROR, array('message' => $exception->getMessage(), 1415797974));
			$this->forward('errorMessage');
		} catch (AuthenticationRequiredException $exception) {
			$this->redirect('authenticate');
		}
	}

	/**
	 * Get profiles grouped by account and webproperty
	 *
	 * TODO Handle "(403) User does not have any Google Analytics account."
	 *
	 * @return array
	 */
	protected function getGroupedProfiles() {
		$this->analytics->requireAuthentication();

		$groupedProfiles = array();
		$accounts = $this->analytics->management_accounts->listManagementAccounts();
		foreach ($accounts as $account) {
			$groupedProfiles[$account->getId()]['label'] = $account->getName();
			$groupedProfiles[$account->getId()]['items'] = array();
		}
		$webproperties = $this->analytics->management_webproperties->listManagementWebproperties('~all');
		$webpropertiesById = array();
		foreach ($webproperties as $webproperty) {
			$webpropertiesById[$webproperty->getId()] = $webproperty;
		}
		$profiles = $this->analytics->management_profiles->listManagementProfiles('~all', '~all');
		foreach ($profiles as $profile) {
			if (isset($webpropertiesById[$profile->getWebpropertyId()])) {
				$webproperty = $webpropertiesById[$profile->getWebpropertyId()];
				$groupedProfiles[$profile->getAccountId()]['items'][$profile->getId()] = array('label' => $webproperty->getName() . ' > ' . $profile->getName(), 'value' => $profile->getId());
			}
		}
		return $groupedProfiles;
	}

	/**
	 * Remove query arguments from the given URI
	 *
	 * @param string $redirectUri
	 * @return string
	 */
	protected function removeUriQueryArguments($redirectUri) {
		$uri = new \TYPO3\Flow\Http\Uri($redirectUri);
		$uri->setQuery(NULL);
		$redirectUri = (string)$uri;
		return $redirectUri;
	}
}