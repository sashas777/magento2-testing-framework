<?php
/*
 * @author     The S Group <support@sashas.org>
 * @copyright  2023  Sashas IT Support Inc. (https://www.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */

declare(strict_types=1);

namespace Magento\Test;

use Magento\Framework\App\Utility\AggregateInvoker;

/**
 * Class CustomAggregateInvoker
 * Do not show debug trace
 */
class CustomAggregateInvoker extends AggregateInvoker
{
    /**
     * @param \Exception $exception
     * @param string $dataSetName
     * @param mixed $dataSet
     * @return string
     */
    protected function prepareMessage(\Exception $exception, $dataSetName, $dataSet)
    {
        if (!is_string($dataSetName)) {
            $dataSetName = var_export($dataSet, true);
        }
        if ($exception instanceof \PHPUnit\Framework\AssertionFailedError
            && !$exception instanceof \PHPUnit\Framework\IncompleteTestError
            && !$exception instanceof \PHPUnit\Framework\SkippedTestError
            || $this->_options['verbose']) {
            $dataSetName = 'Data set: ' . $dataSetName . PHP_EOL;
        } else {
            $dataSetName = '';
        }
        return $dataSetName . $exception->getMessage() . PHP_EOL;
         //      . \PHPUnit\Util\Filter::getFilteredStacktrace($exception);
    }
}