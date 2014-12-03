<?php
namespace TYPO3\Neos\GoogleAnalytics\Service\DataSource;

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
use TYPO3\Neos\GoogleAnalytics\Exception;
use TYPO3\Neos\Service\DataSource\AbstractDataSource;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;

class GoogleAnalyticsDataSource extends AbstractDataSource {

	/**
	 * @var string
	 */
	static protected $identifier = 'GoogleAnalytics';

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Neos\GoogleAnalytics\Service\Reporting
	 */
	protected $reporting;

	/**
	 * Get analytics stats for the given node
	 *
	 * {@inheritdoc}
	 */
	public function getData(NodeInterface $node = NULL, array $arguments) {
		if (!isset($arguments['stat'])) {
			throw new \InvalidArgumentException('Missing "stat" argument', 1416864525);
		}

		$startDateArgument = isset($arguments['startDate']) ? $arguments['startDate'] : '3 months ago';
		$endDateArgument = isset($arguments['endDate']) ? $arguments['endDate'] : '1 day ago';
		try {
			$startDate = new \DateTime($startDateArgument);
		} catch (\Exception $exception) {
			return array('error' => array('message' => 'Invalid date format for argument "startDate"', 'code' => 1417435564));
		}
		try {
			$endDate = new \DateTime($endDateArgument);
		} catch (\Exception $exception) {
			return array('error' => array('message' => 'Invalid date format for argument "endDate"', 'code' => 1417435581));
		}
		try {
			$stats = $this->reporting->getNodeStat($node, $this->controllerContext, $arguments['stat'], $startDate, $endDate);
			$data = array(
				'data' => $stats
			);
			return $data;
		} catch (\Google_Service_Exception $exception) {
			$errors = $exception->getErrors();
			return array(
				'error' => array(
					'message' => isset($errors[0]['message']) ? $errors[0]['message'] : 'Google API returned error',
					'code' => isset($errors[0]['reason']) ? $errors[0]['reason'] : 1417606128
				)
			);
		} catch (Exception $exception) {
			return array(
				'error' => array(
					'message' => $exception->getMessage(),
					'code' => $exception->getCode()
				)
			);
		}
	}

}