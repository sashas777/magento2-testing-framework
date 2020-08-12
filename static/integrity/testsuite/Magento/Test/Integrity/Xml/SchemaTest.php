<?php
/**
 * @author     The S Group <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (https://www.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */
declare(strict_types=1);

namespace Magento\Test\Integrity\Xml;

use Magento\Framework\Component\ComponentRegistrar;

class SchemaTest extends \PHPUnit\Framework\TestCase
{
    public function testXmlFiles()
    {
        $invoker = new \Magento\Framework\App\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * @param string $filename
             */
            function ($filename) {
                $dom = new \DOMDocument();
                if (strpos($filename, 'phpunit.xml')) {
                    return;
                }
                if (strpos($filename, 'csp_whitelist.xml')) {
                    return;
                }
                $xmlFile = file_get_contents($filename);
                $this->assertNotEmpty($xmlFile, $filename. ' - should not be empty.');
                $dom->loadXML($xmlFile);
                $errors = libxml_get_errors();
                libxml_clear_errors();
                $this->assertEmpty($errors, print_r($errors, true));

                $schemaLocations = [];
                preg_match('/xsi:noNamespaceSchemaLocation=\s*"(urn:[^"]+)"/s', $xmlFile, $schemaLocations);
                $this->assertEquals(
                    2,
                    count($schemaLocations),
                    'The XML file at ' . $filename . ' does not have a schema properly defined.  It should '
                    . 'have a xsi:noNamespaceSchemaLocation attribute defined with a URN path.  E.g. '
                    . 'xsi:noNamespaceSchemaLocation="urn:magento:framework:Relative_Path/something.xsd"'
                );

                try {
                    $errors = \Magento\Framework\Config\Dom::validateDomDocument($dom, $schemaLocations[1]);
                } catch (\Exception $exception) {
                    $errors = [$exception->__toString()];
                }
                $this->assertEmpty(
                    $errors,
                    "Error validating $filename against {$schemaLocations[1]}\n" . print_r($errors, true)
                );
            },
            $this->getXmlFiles()
        );
    }

    public function getXmlFiles()
    {
        return $this->_dataSet($this->_getFiles(BP, '*.xml'));
    }

    protected function _getFiles($dir, $pattern, $skipDirPattern = '')
    {
        if (preg_match('/\/Test$/', $dir) || preg_match('/\/vendor$/', $dir) || preg_match('/\/test-reports$/', $dir)) {
            return [];
        }
        $files = glob($dir . '/' . $pattern, GLOB_NOSORT);

        if (empty($skipDirPattern) || !preg_match($skipDirPattern, $dir)) {
            foreach (glob($dir . '/*', GLOB_ONLYDIR | GLOB_NOSORT) as $newDir) {
                $files = array_merge($files, $this->_getFiles($newDir, $pattern, $skipDirPattern));
            }
        }
        return $files;
    }

    /**
     * Files that are exempt from validation
     *
     * @param array &$files
     */
    private function _filterSpecialCases(&$files)
    {
        $list = [
            '#etc/countries.xml$#',
            '#etc/csp_whitelist.xml$#',
            '#conf/schema.xml$#',
            '#layout/swagger_index_index.xml$#',
            '#Doc/etc/doc/vars.xml$#',
            '#phpunit.xml$#',
            '#etc/db_schema.xml$#',
            '#Test/Mftf#',
        ];
        foreach ($list as $pattern) {
            foreach ($files as $key => $value) {
                if (preg_match($pattern, $value)) {
                    unset($files[$key]);
                }
            }
        }
    }

    protected function _dataSet($files)
    {
        $data = [];
        foreach ($files as $file) {
            $data[substr($file, strlen(BP))] = [$file];
        }
        return $data;
    }
}
