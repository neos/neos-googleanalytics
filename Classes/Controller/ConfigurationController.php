<?php
namespace Neos\GoogleAnalytics\Controller;

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
use Neos\GoogleAnalytics\Exception\AuthenticationRequiredException;
use Neos\GoogleAnalytics\Exception\MissingConfigurationException;

/**
 * The ConfigurationController handles the configuration of the Google Analytics module,
 * including connecting to the Google API via OAuth2 and assigning profiles to sites in Neos.
 */
class ConfigurationController extends \Neos\Flow\Mvc\Controller\ActionController
{
    /**
     * @Flow\Inject
     * @var \Neos\Neos\Domain\Repository\SiteRepository
     */
    protected $siteRepository;

    /**
     * @Flow\Inject
     * @var \Neos\GoogleAnalytics\Domain\Repository\SiteConfigurationRepository
     */
    protected $siteConfigurationRepository;

    /**
     * @Flow\Inject
     * @var \Neos\GoogleAnalytics\Service\TokenStorage
     */
    protected $tokenStorage;

    /**
     * @Flow\Inject
     * @var \Neos\GoogleAnalytics\Service\GoogleAnalytics
     */
    protected $analytics;

    /**
     * Show a list of sites and assigned GA profiles
     *
     * @return void
     */
    public function indexAction()
    {
        $siteConfigurations = $this->siteConfigurationRepository->findAll();

        $sites = $this->siteRepository->findAll();
        $sitesWithConfiguration = [];
        foreach ($sites as $site) {
            $item = ['site' => $site];
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
     * @param array<\Neos\GoogleAnalytics\Domain\Model\SiteConfiguration> $siteConfigurations Array of site configurations
     * @return void
     */
    public function updateAction(array $siteConfigurations)
    {
        foreach ($siteConfigurations as $siteConfiguration) {
            if ($this->persistenceManager->isNewObject($siteConfiguration)) {
                $this->siteConfigurationRepository->add($siteConfiguration);
            } else {
                $this->siteConfigurationRepository->update($siteConfiguration);
            }
        }

        $this->emitSiteConfigurationChanged();

        $this->addFlashMessage('Configuration has been updated.', 'Update', null, [], 1417109043);
        $this->redirect('index');
    }

    /**
     * @return void
     */
    public function authenticateAction()
    {
        $client = $this->analytics->getClient();

        $redirectUri = $this->uriBuilder->reset()
            ->setCreateAbsoluteUri(true)
            ->uriFor('authenticate');
        $client->setRedirectUri($this->removeUriQueryArguments($redirectUri));

        // We have to get the "code" query argument without a module prefix
        $code = $this->request->getHttpRequest()->getArgument('code');
        if (!empty($code)) {
            $client->authenticate($code);

            $this->tokenStorage->storeAccessToken(json_encode($client->getAccessToken()));
            $this->tokenStorage->storeRefreshToken($client->getRefreshToken());

            $indexUri = $this->uriBuilder->reset()
                ->setCreateAbsoluteUri(true)
                ->uriFor('index');
            $this->redirectToUri($this->removeUriQueryArguments($indexUri));
        }

        // If we don't have a refresh token, require an approval prompt to receive a refresh token
        $refreshToken = $this->tokenStorage->getRefreshToken();
        if ($refreshToken === null) {
            $client->setApprovalPrompt('force');
        }

        $authUrl = $client->createAuthUrl();
        $this->view->assign('authUrl', $authUrl);
    }

    /**
     * Logout (disconnect) the Google account
     *
     * @return void
     */
    public function logoutAction()
    {
        $this->tokenStorage->removeTokens();
        $this->addFlashMessage('Account has been disconnected.', 'Disconnect', null, [], 1417607416);
        $this->redirect('index');
    }

    /**
     * @return void
     */
    public function errorMessageAction()
    {
        $client = $this->analytics->getClient();

        if ($client instanceof \Google_Client) {
            $authenticated = $client->getAccessToken() !== null;
        } else {
            $authenticated = false;
        }
        $this->view->assign('authenticated', $authenticated);
    }

    /**
     * Catch Google service exceptions and forward to the "apiError" action to show
     * an error message.
     *
     * @return void
     */
    protected function callActionMethod()
    {
        try {
            parent::callActionMethod();
        } catch (\Google_Service_Exception $exception) {
            $this->addFlashMessage('%1$s', 'Google API error', \Neos\Error\Messages\Message::SEVERITY_ERROR, ['message' => $exception->getMessage(), 1415797974]);
            $this->forward('errorMessage');
        } catch (MissingConfigurationException $exception) {
            $this->addFlashMessage('%1$s', 'Missing configuration', \Neos\Error\Messages\Message::SEVERITY_ERROR, ['message' => $exception->getMessage(), 1415797974]);
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
    protected function getGroupedProfiles()
    {
        $this->analytics->requireAuthentication();

        $groupedProfiles = [];
        $accounts = $this->analytics->management_accounts->listManagementAccounts();
        foreach ($accounts as $account) {
            $groupedProfiles[$account->getId()]['label'] = $account->getName();
            $groupedProfiles[$account->getId()]['items'] = [];
        }
        $webproperties = $this->analytics->management_webproperties->listManagementWebproperties('~all');
        $webpropertiesById = [];
        foreach ($webproperties as $webproperty) {
            $webpropertiesById[$webproperty->getId()] = $webproperty;
        }
        $profiles = $this->analytics->management_profiles->listManagementProfiles('~all', '~all');
        foreach ($profiles as $profile) {
            if (isset($webpropertiesById[$profile->getWebpropertyId()])) {
                $webproperty = $webpropertiesById[$profile->getWebpropertyId()];
                $groupedProfiles[$profile->getAccountId()]['items'][$profile->getId()] = ['label' => $webproperty->getName() . ' > ' . $profile->getName(), 'value' => $profile->getId()];
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
    protected function removeUriQueryArguments($redirectUri)
    {
        $uri = new \Neos\Flow\Http\Uri($redirectUri);
        $uri->setQuery(null);
        $redirectUri = (string)$uri;

        return $redirectUri;
    }

    /**
     * @Flow\Signal
     * @return void
     */
    protected function emitSiteConfigurationChanged()
    {
    }
}
