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
        $invoker = new \Magento\Framework\App\Utility\AggregateInvoker($this);
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
        $this->assertNotRegExp('/" :\s*["{]/', $contents, 'Coding style: there should be no space before colon.');
        $this->assertNotRegExp('/":["{]/', $contents, 'Coding style: a space is necessary after colon.');
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
        $this->assertObjectHasAttribute('name', $json);
        $this->assertObjectHasAttribute('license', $json);
        $this->assertObjectHasAttribute('type', $json);
        $this->assertObjectHasAttribute('require', $json);
        $packageType = $json->type;

        switch ($packageType) {
            case 'magento2-module':
                $xml = simplexml_load_file("$dir/etc/module.xml");
                $this->assertDependsOnPhp($json->require);
                $this->assertDependsOnFramework($json->require);
                $this->assertAutoload($json);
                $this->assertVersionSpecified($json);
                break;
            case 'magento2-language':
                $this->assertDependsOnFramework($json->require);
                $this->assertVersionSpecified($json);
                break;
            case 'magento2-theme':
                $this->assertDependsOnPhp($json->require);
                $this->assertDependsOnFramework($json->require);
                $this->assertVersionSpecified($json);
                break;
            case 'magento2-library':
                $this->assertDependsOnPhp($json->require);
                $this->assertAutoload($json);
                $this->assertVersionSpecified($json);
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
    private function assertVersionSpecified(\StdClass $json)
    {
        $errorMessage = 'Version must be specified in the root and package composer JSON files';
        $this->assertObjectHasAttribute('version', $json, $errorMessage);
    }

    /**
     * Assert that there is PSR-4 autoload in composer json
     *
     * @param \StdClass $json
     */
    private function assertAutoload(\StdClass $json)
    {
        $errorMessage = 'There must be an "autoload->psr-4" section in composer.json of each Magento component.';
        $this->assertObjectHasAttribute('autoload', $json, $errorMessage);
        $this->assertObjectHasAttribute('psr-4', $json->autoload, $errorMessage);
    }

    /**
     * Make sure a component depends on php version
     *
     * @param \StdClass $json
     */
    private function assertDependsOnPhp(\StdClass $json)
    {
        $this->assertObjectHasAttribute('php', $json, 'This component is expected to depend on certain PHP version(s)');
    }

    /**
     * Make sure a component depends on magento/framework component
     *
     * @param \StdClass $json
     */
    private function assertDependsOnFramework(\StdClass $json)
    {
        $this->assertObjectHasAttribute(
            'magento/framework',
            $json,
            'This component is expected to depend on magento/framework'
        );
    }
}
