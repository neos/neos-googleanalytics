<?php
declare(strict_types=1);

namespace Neos\GoogleAnalytics\Service\DataSource;

/*
 * This file is part of the Neos.GoogleAnalytics package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use DateTime;
use InvalidArgumentException;
use Neos\Flow\Annotations as Flow;
use Neos\GoogleAnalytics\Exception;
use Neos\GoogleAnalytics\Service\Reporting;
use Neos\Neos\Service\DataSource\AbstractDataSource;
use Neos\ContentRepository\Domain\Model\NodeInterface;

class GoogleAnalyticsDataSource extends AbstractDataSource
{
    /**
     * @var string
     */
    protected static $identifier = 'GoogleAnalytics';

    /**
     * @Flow\Inject
     * @var Reporting
     */
    protected $reporting;

    /**
     * Get analytics stats for the given node
     *
     * {@inheritdoc}
     */
    public function getData(NodeInterface $node = null, array $arguments = []): array
    {
        if (!isset($arguments['stat'])) {
            throw new InvalidArgumentException('Missing "stat" argument', 1416864525);
        }

        $startDateArgument = isset($arguments['startDate']) ? $arguments['startDate'] : '3 months ago';
        $endDateArgument = isset($arguments['endDate']) ? $arguments['endDate'] : '1 day ago';
        try {
            $startDate = new DateTime($startDateArgument);
        } catch (\Exception $exception) {
            return ['error' => ['message' => 'Invalid date format for argument "startDate"', 'code' => 1417435564]];
        }
        try {
            $endDate = new DateTime($endDateArgument);
        } catch (\Exception $exception) {
            return ['error' => ['message' => 'Invalid date format for argument "endDate"', 'code' => 1417435581]];
        }
        try {
            $stats = $this->reporting->getNodeStat($node, $this->controllerContext, $arguments['stat'], $startDate, $endDate);
            $data = [
                'data' => $stats
            ];

            return $data;
        } catch (Exception $exception) {
            return [
                'error' => [
                    'message' => $exception->getMessage(),
                    'code' => $exception->getCode()
                ]
            ];
        }
    }
}
