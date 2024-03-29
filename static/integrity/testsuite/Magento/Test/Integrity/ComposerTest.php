<?php
/**
 * @author     The S Group <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (https://www.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */
declare(strict_types=1);

namespace Magento\Test\Integrity;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Composer\MagentoComponent;

/**
 * A test that enforces validity of composer.json files and any other conventions in Magento components
 */
class ComposerTest extends \PHPUnit\Framework\TestCase
{
    public function testValidComposerJson()
    {
        $invoker = new \Magento\Test\CustomAggregateInvoker($this);
        $invoker(
            /**
             * @param string $dir
             */
            function ($dir) {
                $file = $dir . '/composer.json';
                $this->assertFileExists($file);
                $contents = file_get_contents($file);
                $json = json_decode($contents);
                $this->assertCodingStyle($contents);
                $this->assertMagentoConventions($dir, $json);
            },
            $this->validateComposerJsonDataProvider()
        );
    }

    /**
     * @return array
     */
    public function validateComposerJsonDataProvider()
    {
        $result[BP] = [BP];
        return $result;
    }

    /**
     * Some of coding style conventions
     *
     * @param string $contents
     */
    private function assertCodingStyle($contents)
    {
        $this->assertDoesNotMatchRegularExpression('/" :\s*["{]/', $contents, 'Coding style: there should be no space before colon.');
        $this->assertDoesNotMatchRegularExpression('/":["{]/', $contents, 'Coding style: a space is necessary after colon.');
    }

    /**
     * Enforce Magento-specific conventions to a composer.json file
     *
     * @param string $dir
     * @param \StdClass $json
     * @throws \InvalidArgumentException
     */
    private function assertMagentoConventions($dir, \StdClass $json)
    {
        $this->assertTrue(property_exists($json, 'name'));
        $this->assertTrue(property_exists($json, 'type'));
        $this->assertTrue(property_exists($json, 'require'));

        $packageType = $json->type;

        switch ($packageType) {
            case 'magento2-module':
                $xml = simplexml_load_file("$dir/etc/module.xml");
                $this->assertDependsOnPhp($json->require);
                $this->assertDependsOnFramework($json->require);
                $this->assertAutoload($json);
                break;
            case 'magento2-language':
                $this->assertDependsOnFramework($json->require);
                break;
            case 'magento2-theme':
                $this->assertDependsOnPhp($json->require);
                $this->assertDependsOnFramework($json->require);
                break;
            case 'magento2-library':
                $this->assertDependsOnPhp($json->require);
                $this->assertAutoload($json);
                break;
            default:
                throw new \InvalidArgumentException("Unknown package type {$packageType}");
        }
    }

    /**
     * Version must be specified in the root and package composer JSON files.
     *
     * @param \StdClass $json
     */
    private function assertVersionNotSpecified(\StdClass $json)
    {
        $errorMessage = 'Version must not be specified in the root and package composer JSON files';
        $this->assertObjectNotHasAttribute('version', $json, $errorMessage);
    }

    /**
     * Assert that there is PSR-4 autoload in composer json
     *
     * @param \StdClass $json
     */
    private function assertAutoload(\StdClass $json)
    {
        $errorMessage = 'There must be an "autoload->psr-4" section in composer.json of each Magento component.';
        $this->assertTrue(property_exists($json->autoload, 'psr-4'), $errorMessage);
    }

    /**
     * Make sure a component depends on php version
     *
     * @param \StdClass $json
     */
    private function assertDependsOnPhp(\StdClass $json)
    {
        $this->assertTrue(property_exists($json, 'php'), 'This component is expected to depend on certain PHP version(s)');
    }

    /**
     * Make sure a component depends on magento/framework component
     *
     * @param \StdClass $json
     */
    private function assertDependsOnFramework(\StdClass $json)
    {
        $this->assertTrue(property_exists($json, 'magento/framework'), 'This component is expected to depend on magento/framework');
    }
}
