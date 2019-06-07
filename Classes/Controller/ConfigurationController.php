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
use Neos\Flow\Mvc\View\ViewInterface;
use Neos\Fusion\View\FusionView;
use Neos\GoogleAnalytics\Service\GoogleAnalytics;
use Neos\Neos\Controller\Module\AbstractModuleController;

/**
 * The ConfigurationController shows the current configuration for all configured sites
 * and possible issues if they exist.
 */
class ConfigurationController extends AbstractModuleController
{
    /**
     * @var FusionView
     */
    protected $view;

    /**
     * @var string
     */
    protected $defaultViewObjectName = FusionView::class;

    /**
     * @Flow\Inject
     * @var GoogleAnalytics
     */
    protected $analytics;

    /**
     * Show a list of sites and assigned GA profiles
     *
     * @return void
     */
    public function indexAction()
    {
        $this->view->assignMultiple([
            'sitesConfiguration' => $this->settings['sites'],
        ]);
    }

    /**
     * Sets the Fusion path pattern on the view.
     *
     * @param ViewInterface $view
     * @return void
     */
    protected function initializeView(ViewInterface $view)
    {
        parent::initializeView($view);

        /** @var FusionView $view */
        $view->disableFallbackView();
        $view->setFusionPathPatterns(['resource://@package/Private/BackendFusion']);
        $view->setFusionPathPattern('resource://@package/Private/BackendFusion');
    }
}
