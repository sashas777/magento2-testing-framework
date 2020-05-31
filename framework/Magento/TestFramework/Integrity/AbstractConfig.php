<?php
/**
 * An abstract test class for XML/XSD validation
 * @author     The S Group <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (https://www.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */

declare(strict_types=1);

namespace Magento\TestFramework\Integrity;

abstract class AbstractConfig extends \PHPUnit\Framework\TestCase
{
    public function testXmlFiles()
    {
        if (null === $this->_getXmlName()) {
            $this->markTestSkipped('No XML validation of files requested');
        }
        $invoker = new \Magento\Framework\App\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * @param string $configFile
             */
            function ($configFile) {
                if (strpos($configFile, '/vendor/')) {
                    return;
                }
                $this->_validateFileExpectSuccess($configFile, $this->_getXsd(), $this->_getFileXsd());
            },
            \Magento\Framework\App\Utility\Files::init()->getConfigFiles($this->_getXmlName())
        );
    }

    /**
     * Run schema validation against a known bad xml file with a provided schema.
     *
     * This helper expects the validation to fail and will fail a test if no errors are found.
     *
     * @param $xmlFile string a known bad xml file.
     * @param $schemaFile string schema that should find errors in the known bad xml file.
     * @param $fileSchemaFile string schema that should find errors in the known bad xml file
     */
    protected function _validateFileExpectSuccess($xmlFile, $schemaFile, $fileSchemaFile = null)
    {
        $dom = new \DOMDocument();
        $dom->loadXML(file_get_contents($xmlFile));
        $errors = \Magento\Framework\Config\Dom::validateDomDocument($dom, $schemaFile);
        if ($errors) {
            if ($fileSchemaFile !== null) {
                $moreErrors = \Magento\Framework\Config\Dom::validateDomDocument($dom, $fileSchemaFile);
                if (empty($moreErrors)) {
                    return;
                } else {
                    $errors = array_merge($errors, $moreErrors);
                }
            }
            $this->fail(
                'There is a problem with the schema.  A known good XML file failed validation: ' . PHP_EOL . implode(
                    PHP_EOL . PHP_EOL,
                    $errors
                )
            );
        }
    }

    /**
     * Returns the name of the XSD file to be used to validate the XML
     *
     * @return string
     */
    abstract protected function _getXsd();

    /**
     * Returns the name of the XSD file to be used to validate partial XML
     *
     * @return string
     */
    abstract protected function _getFileXsd();

    /**
     * Returns the name of the xml files to validate
     *
     * @return string
     */
    abstract protected function _getXmlName();
}
