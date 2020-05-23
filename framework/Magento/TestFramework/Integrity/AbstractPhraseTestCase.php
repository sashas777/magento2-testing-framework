<?php
/**
 * @author     The S Group <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (https://www.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */

/**
 * Abstract class for phrase testing
 */
declare(strict_types=1);

namespace Magento\TestFramework\Integrity;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Setup\Module\I18n\FilesCollector;

class AbstractPhraseTestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @param array $phrase
     * @return string
     */
    protected function _createPhraseError($phrase)
    {
        return "\nPhrase: {$phrase['phrase']} \nFile: {$phrase['file']} \nLine: {$phrase['line']}";
    }

    /**
     * @param array $phrase
     * @return string
     */
    protected function _createMissedPhraseError($phrase)
    {
        return "\nMissed Phrase: File: {$phrase['file']} \nLine: {$phrase['line']}";
    }

    /**
     * @return \RegexIterator
     */
    protected function _getFiles()
    {
        $filesCollector = new FilesCollector();
        $componentRegistrar = new ComponentRegistrar();
        $paths = array_merge(
            $componentRegistrar->getPaths(ComponentRegistrar::MODULE),
            $componentRegistrar->getPaths(ComponentRegistrar::LIBRARY)
        );
        return $filesCollector->getFiles(
            $paths,
            '/\.(php|phtml)$/'
        );
    }
}
