<?php
/*
 * @author     The S Group <support@sashas.org>
 * @copyright  2023  Sashas IT Support Inc. (https://www.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */
declare(strict_types=1);

namespace TheSGroup\StaticTests;

use PHPUnit\Framework\TestFailure;
use PHPUnit\TextUI\DefaultResultPrinter;
use PHPUnit\TextUI\ResultPrinter;

class CustomResultPrinter extends DefaultResultPrinter implements ResultPrinter
{
    protected function printDefect(TestFailure $defect, int $count): void
    {
        $this->printDefectHeader($defect, $count);
        if ($this->debug) {
            $this->printDefectTrace($defect);
        }
    }
}
