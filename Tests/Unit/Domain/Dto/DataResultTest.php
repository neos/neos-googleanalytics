<?php
namespace TYPO3\Neos\GoogleAnalytics\Tests\Unit\Domain\Dto;

/*                                                                            *
 * This script belongs to the TYPO3 Flow package "TYPO3.Neos.GoogleAnalytics" *
 *                                                                            *
 * It is free software; you can redistribute it and/or modify it under        *
 * the terms of the GNU General Public License, either version 3 of the       *
 * License, or (at your option) any later version.                            *
 *                                                                            *
 * The TYPO3 project - inspiring people to share!                             *
 *                                                                            */

use TYPO3\Flow\Tests\UnitTestCase;
use TYPO3\Neos\GoogleAnalytics\Domain\Dto\DataResult;

class DataResultTest extends UnitTestCase {

	/**
	 * @test
	 */
	public function jsonSerializeWithZeroTotalReturnsZeroPercent() {
		$data = new \Google_Service_Analytics_GaData();
		$data->setColumnHeaders(array(
			array('name' => 'ga:userType', 'columnType' => 'DIMENSION'),
			array('name' => 'ga:sessions', 'columnType' => 'METRIC')
		));
		$data->setTotalsForAllResults(array(
			'ga:sessions' => 0
		));
		$data->setRows(array(
			array(0, 0)
		));

		$result = new DataResult($data);
		$serializableValue = $result->jsonSerialize();

		$this->assertArrayHasKey('rows', $serializableValue);
		$this->assertArrayHasKey(0, $serializableValue['rows']);
		$this->assertArrayHasKey('percent', $serializableValue['rows'][0]);
		$this->assertSame(0, $serializableValue['rows'][0]['percent'], 'Value for rows.0.percent should match');
	}
}