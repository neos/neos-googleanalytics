<?php
declare(strict_types=1);

namespace Neos\GoogleAnalytics\Domain\Dto;

/*
 * This file is part of the Neos.GoogleAnalytics package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

/**
 * A data result to wrap Google Analytics data results for conversion
 */
class DataResult implements \JsonSerializable
{
    /**
     * @var \Google_Service_Analytics_GaData
     */
    protected $result;

    /**
     * @param \Google_Service_Analytics_GaData $result
     */
    public function __construct(\Google_Service_Analytics_GaData $result)
    {
        $this->result = $result;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        $totals = $this->result->getTotalsForAllResults();

        $sanitizedTotals = [];
        foreach ($totals as $key => $value) {
            $replacedKey = str_replace(':', '_', $key);
            $sanitizedTotals[$replacedKey] = $value;
        }
        $columnHeaders = $this->result->getColumnHeaders();
        foreach ($columnHeaders as &$columnHeader) {
            $columnHeader['name'] = str_replace(':', '_', $columnHeader['name']);
        }
        $rows = $this->result->getRows();
        if (!is_array($rows)) {
            $rows = [];
        }
        $sanitizedRows = [];
        foreach ($rows as $rowIndex => $row) {
            foreach ($row as $columnIndex => $columnValue) {
                $columnName = $columnHeaders[$columnIndex]['name'];
                if ($columnName === 'ga_date') {
                    $columnValue = substr($columnValue, 0, 4) . '-' . substr($columnValue, 4, 2) . '-' . substr($columnValue, 6, 2);
                }
                $sanitizedRows[$rowIndex][$columnName] = $columnValue;
            }
            // The simple case that we have 2 columns with 1 dimension and 1 metric
            if (count($columnHeaders) == 2 && $columnHeaders[0]['columnType'] === 'DIMENSION' && $columnHeaders[1]['columnType'] === 'METRIC') {
                $sanitizedTotal = $sanitizedTotals[$columnHeaders[1]['name']];
                if ($sanitizedTotal > 0) {
                    $sanitizedRows[$rowIndex]['percent'] = round($row[1] / $sanitizedTotal * 100, 2);
                } else {
                    $sanitizedRows[$rowIndex]['percent'] = 0;
                }
            }
        }

        return [
            'totals' => $sanitizedTotals,
            'rows' => $sanitizedRows
        ];
    }
}
