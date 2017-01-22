<?php
namespace Neos\GoogleAnalytics\Tests\Unit\Domain\Dto;

/*
 * This file is part of the Neos.GoogleAnalytics package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Tests\UnitTestCase;
use Neos\GoogleAnalytics\Domain\Dto\DataResult;

class DataResultTest extends UnitTestCase
{
    /**
     * @test
     */
    public function jsonSerializeWithZeroTotalReturnsZeroPercent()
    {
        $data = new \Google_Service_Analytics_GaData();
        $data->setColumnHeaders([
            ['name' => 'ga:userType', 'columnType' => 'DIMENSION'],
            ['name' => 'ga:sessions', 'columnType' => 'METRIC']
        ]);
        $data->setTotalsForAllResults([
            'ga:sessions' => 0
        ]);
        $data->setRows([
            [0, 0]
        ]);

        $result = new DataResult($data);
        $serializableValue = $result->jsonSerialize();

        $this->assertArrayHasKey('rows', $serializableValue);
        $this->assertArrayHasKey(0, $serializableValue['rows']);
        $this->assertArrayHasKey('percent', $serializableValue['rows'][0]);
        $this->assertSame(0, $serializableValue['rows'][0]['percent'], 'Value for rows.0.percent should match');
    }
}
