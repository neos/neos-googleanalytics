<?php
namespace TYPO3\Neos\GoogleAnalytics\Domain\Dto;

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

/**
 * A data result to wrap Google Analytics data results for conversion
 */
class DataResult implements \JsonSerializable {

	/**
	 * @var \Google_Service_Analytics_GaData
	 */
	protected $result;

	/**
	 * @param \Google_Service_Analytics_GaData $result
	 */
	public function __construct(\Google_Service_Analytics_GaData $result) {
		$this->result = $result;
	}

	/**
	 * {@inheritdoc}
	 */
	function jsonSerialize() {
		$totals = $this->result->getTotalsForAllResults();

		$sanitizedTotals = array();
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
			$rows = array();
		}
		$sanitizedRows = array();
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

		return array(
			'totals' => $sanitizedTotals,
			'rows' => $sanitizedRows
		);
	}

}