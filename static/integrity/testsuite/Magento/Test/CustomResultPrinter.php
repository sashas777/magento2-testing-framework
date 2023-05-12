<?php
/*
 * @author     The S Group <support@sashas.org>
 * @copyright  2023  Sashas IT Support Inc. (https://www.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */
declare(strict_types=1);

namespace Magento\Test;

use PHPUnit\Framework\TestFailure;
use PHPUnit\TextUI\DefaultResultPrinter;
use PHPUnit\TextUI\ResultPrinter;

/**
 * Class CustomResultPrinter
 * Do not show debug trace
 */
class CustomResultPrinter extends DefaultResultPrinter implements ResultPrinter
{
    /**
     * @param TestFailure $defect
     * @param int $count
     *
     * @return void
     */
    protected function printDefect(TestFailure $defect, int $count): void
    {
        $this->printDefectHeader($defect, $count);
        if ($this->debug) {
            $this->printDefectTrace($defect);
        } else {
            $this->writeln((string) $defect->exceptionMessage());
        }
    }
}
