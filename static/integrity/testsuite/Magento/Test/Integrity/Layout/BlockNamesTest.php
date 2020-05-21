<?php
/**
 * Test block names exists
 * @author     The S Group <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (https://www.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */
declare(strict_types=1);

namespace Magento\Test\Integrity\Layout;

class BlockNamesTest extends \PHPUnit\Framework\TestCase
{
    public function testBlocksHasName()
    {
        $invoker = new \Magento\Framework\App\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * Test validate that blocks without name doesn't exist in layout file
             *
             * @param string $layoutFile
             */
            function ($layoutFile) {
                if (strpos($layoutFile, '/vendor/')) {
                    return;
                }
                $dom = new \DOMDocument();
                $dom->load($layoutFile);
                $xpath = new \DOMXpath($dom);
                $count = $xpath->query('//block[not(@name)]')->length;

                if ($count) {
                    $this->fail('Following file contains ' . $count . ' blocks without name. ' .
                        'File Path:' . "\n" . $layoutFile);
                }
            },
            \Magento\Framework\App\Utility\Files::init()->getLayoutFiles()
        );
    }
}
