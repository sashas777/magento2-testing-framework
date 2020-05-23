<?php
/**
 * @author     The S Group <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (https://www.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */
declare(strict_types=1);
/**
 * Scan source code for detects invocations of outdated __() method
 */
namespace Magento\Test\Integrity\Phrase\Legacy;

use Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer;
use Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer\Translate\MethodCollector;
use Magento\TestFramework\Integrity\AbstractPhraseTestCase;

class SignatureTest extends AbstractPhraseTestCase
{
    /**
     * @var \Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer\Translate\MethodCollector
     */
    protected $_phraseCollector;

    protected function setUp()
    {
        $this->_phraseCollector = new MethodCollector(
            new Tokenizer()
        );
    }

    public function testSignature()
    {
        $errors = [];
        foreach ($this->_getFiles() as $file) {
            $this->_phraseCollector->parse($file);
            foreach ($this->_phraseCollector->getPhrases() as $phrase) {
                $errors[] = $this->_createPhraseError($phrase);
            }
        }
        $this->assertEmpty(
            $errors,
            sprintf(
                '%d usages of the old translation method call were discovered: %s',
                count($errors),
                implode("\n\n", $errors)
            )
        );
    }
}
