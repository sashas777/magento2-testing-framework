<?php
/**
 * Scan source code for references to classes and see if they indeed exist
 * @author     The S Group <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (https://www.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */
declare(strict_types=1);

namespace Magento\Test\Integrity;

use Magento\Framework\App\Utility\Classes;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\App\Utility\Files;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class ClassesTest extends \PHPUnit\Framework\TestCase
{
    /**setUpBeforeClass
     * @var ComponentRegistrar
     */
    private $componentRegistrar;

    /**
     * List of already found classes to avoid checking them over and over again
     *
     * @var array
     */
    private $existingClasses = [];

    /**
     * @var array
     */
    private static $keywordsBlacklist = ["String", "Array", "Boolean", "Element"];

    /**
     * @var array|null
     */
    private $referenceBlackList = null;

    /**
     * Set Up
     */
    protected function setUp(): void
    {
        $this->componentRegistrar = new ComponentRegistrar();
    }

    public function testPhpFiles()
    {
        $invoker = new \Magento\Framework\App\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * @param string $file
             */
            function ($file) {
                if (strpos($file, '/vendor/')) {
                    return;
                }
                $contents = file_get_contents($file);
                $classes = Classes::getAllMatches(
                    $contents,
                    '/
                # ::getResourceModel ::getBlockSingleton ::getModel ::getSingleton
                \:\:get(?:ResourceModel | BlockSingleton | Model | Singleton)?\(\s*[\'"]([a-z\d\\\\]+)[\'"]\s*[\),]

                # various methods, first argument
                | \->(?:initReport | addBlock | createBlock
                    | setAttributeModel | setBackendModel | setFrontendModel | setSourceModel | setModel
                )\(\s*\'([a-z\d\\\\]+)\'\s*[\),]

                # various methods, second argument
                | \->add(?:ProductConfigurationHelper | OptionsRenderCfg)\(.+?,\s*\'([a-z\d\\\\]+)\'\s*[\),]

                # \Mage::helper ->helper
                | (?:Mage\:\:|\->)helper\(\s*\'([a-z\d\\\\]+)\'\s*\)

                # misc
                | function\s_getCollectionClass\(\)\s+{\s+return\s+[\'"]([a-z\d\\\\]+)[\'"]
                | \'resource_model\'\s*=>\s*[\'"]([a-z\d\\\\]+)[\'"]
                | (?:_parentResourceModelName | _checkoutType | _apiType)\s*=\s*\'([a-z\d\\\\]+)\'
                | \'renderer\'\s*=>\s*\'([a-z\d\\\\]+)\'
                /ix'
                );

                // without modifier "i". Starting from capital letter is a significant characteristic of a class name
                Classes::getAllMatches(
                    $contents,
                    '/(?:\-> | parent\:\:)(?:_init | setType)\(\s*
                    \'([A-Z][a-z\d][A-Za-z\d\\\\]+)\'(?:,\s*\'([A-Z][a-z\d][A-Za-z\d\\\\]+)\')
                    \s*\)/x',
                    $classes
                );

                $this->collectResourceHelpersPhp($contents, $classes);

                $this->assertClassesExist($classes, $file);
            },
            Files::init()->getPhpFiles(
                Files::INCLUDE_APP_CODE
                | Files::INCLUDE_PUB_CODE
                | Files::INCLUDE_LIBS
                | Files::INCLUDE_TEMPLATES
                | Files::AS_DATA_SET
                | Files::INCLUDE_NON_CLASSES
            )
        );
    }

    /**
     * Special case: collect resource helper references in PHP-code
     *
     * @param string $contents
     * @param array &$classes
     * @return void
     */
    private function collectResourceHelpersPhp(string $contents, array &$classes): void
    {
        $regex = '/(?:\:\:|\->)getResourceHelper\(\s*\'([a-z\d\\\\]+)\'\s*\)/ix';
        $matches = Classes::getAllMatches($contents, $regex);
        foreach ($matches as $moduleName) {
            $classes[] = "{$moduleName}\\Model\\ResourceModel\\Helper\\Mysql4";
        }
    }

    public function testConfigFiles()
    {
        $invoker = new \Magento\Framework\App\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * @param string $path
             */
            function ($path) {
                if (strpos($path, '/vendor/')) {
                    return;
                }
                $classes = Classes::collectClassesInConfig(simplexml_load_file($path));
                $this->assertClassesExist($classes, $path);
            },
            Files::init()->getMainConfigFiles()
        );
    }

    public function testLayoutFiles()
    {
        $invoker = new \Magento\Framework\App\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * @param string $path
             */
            function ($path) {
                if (strpos($path, '/vendor/')) {
                    return;
                }
                $xml = simplexml_load_file($path);

                $classes = Classes::getXmlNodeValues(
                    $xml,
                    '/layout//*[contains(text(), "\\\\Block\\\\") or contains(text(),
                        "\\\\Model\\\\") or contains(text(), "\\\\Helper\\\\")]'
                );
                foreach (Classes::getXmlAttributeValues(
                    $xml,
                    '/layout//@helper',
                    'helper'
                ) as $class) {
                    $classes[] = Classes::getCallbackClass($class);
                }
                foreach (Classes::getXmlAttributeValues(
                    $xml,
                    '/layout//@module',
                    'module'
                ) as $module) {
                    $classes[] = str_replace('_', '\\', "{$module}_Helper_Data");
                }
                $classes = array_merge($classes, Classes::collectLayoutClasses($xml));

                $this->assertClassesExist(array_unique($classes), $path);
            },
            Files::init()->getLayoutFiles()
        );
    }

    /**
     * Check whether specified classes correspond to a file according PSR-0 standard
     *
     * Cyclomatic complexity is because of temporary marking test as incomplete
     * Suppressing "unused variable" because of the "catch" block
     *
     * @param array $classes
     * @param string $path
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    private function assertClassesExist(array $classes, string $path): void
    {
        if (!$classes) {
            return;
        }
        $badClasses = [];
        $badUsages = [];
        foreach ($classes as $class) {
            $class = trim($class, '\\');
            try {
                if (strrchr($class, '\\') === false && !Classes::isVirtual($class)) {
                    $badUsages[] = $class;
                    continue;
                } else {
                    $this->assertTrue(
                        isset(
                            $this->existingClasses[$class]
                        ) || Files::init()->classFileExists(
                            $class
                        ) || Classes::isVirtual(
                            $class
                        ) || Classes::isAutogenerated(
                            $class
                        )
                    );
                }
                $this->existingClasses[$class] = 1;
            } catch (\PHPUnit\Framework\AssertionFailedError $e) {
                $badClasses[] = '\\' . $class;
            }
        }
        if ($badClasses) {
            $this->fail("Files not found for following usages in {$path}:\n" . implode("\n", $badClasses));
        }
        if ($badUsages) {
            $this->fail("Bad usages of classes in {$path}: \n" . implode("\n", $badUsages));
        }
    }

    public function testClassNamespaces()
    {
        $invoker = new \Magento\Framework\App\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * Assert PHP classes have valid formal namespaces according to file locations
             *
             * @param array $file
             */
            function ($file) {
                if (strpos($file, '/vendor/')) {
                    return;
                }
                $relativePath = str_replace(BP . "/", "", $file);
                // exceptions made for fixture files from tests
                if (strpos($relativePath, '/_files/') !== false) {
                    return;
                }

                $contents = file_get_contents($file);

                $classPattern = '/^(abstract\s)?class\s[A-Z][^\s\/]+/m';

                $classNameMatch = [];
                $className = null;

                // if no class declaration found for $file, then skip this file
                if (preg_match($classPattern, $contents, $classNameMatch) == 0) {
                    return;
                }

                $classParts = explode(' ', $classNameMatch[0]);
                $className = array_pop($classParts);
                $this->assertClassNamespace($file, $relativePath, $contents, $className);
            },
            Files::init()->getPhpFiles()
        );
    }

    /**
     * Assert PHP classes have valid formal namespaces according to file locations
     *
     *
     * @param string $file
     * @param string $relativePath
     * @param string $contents
     * @param string $className
     * @return void
     */
    private function assertClassNamespace(string $file, string $relativePath, string $contents, string $className): void
    {
        $namespacePattern = '/[a-zA-Z]+[^\.]+\/[a-zA-Z]+[^\.]+/';
        $unitTestPattern = '/(tests|test)\/[a-zA-Z]+[^\.]+/';
        $formalPattern = '/^namespace\s[a-zA-Z]+(\\\\[a-zA-Z0-9]+)*/m';

        $namespaceMatch = [];
        $formalNamespaceArray = [];
        $namespaceFolders = null;

        //  skip unit tests
        if (preg_match($unitTestPattern, $relativePath, $namespaceMatch) == 0) {
            return;
        }

        // if no namespace pattern found according to the path of the file, skip the file
        if (preg_match($namespacePattern, $relativePath, $namespaceMatch) == 0) {
            return;
        }

        $namespaceFolders = $namespaceMatch[0];
        $classParts = explode('/', $namespaceFolders);
        //sashas
        $composerFile = BP . '/composer.json';
        $composerContent = file_get_contents($composerFile);
        $composerJson = json_decode($composerContent, true);
        $classParts = array_merge(explode('\\', rtrim(array_key_first($composerJson['autoload']['psr-4']), '\\')), $classParts);

//        array_pop($classParts);
        $expectedNamespace = implode('\\', $classParts);
        $expectedNamespace = str_replace('\\', '/', $expectedNamespace);

        if (preg_match($formalPattern, $contents, $formalNamespaceArray) != 0) {
            $foundNamespace = substr($formalNamespaceArray[0], 10);
            $foundNamespace = str_replace('\\', '/', $foundNamespace);
            $foundNamespace .= '/' . $className;

            if ($namespaceFolders != null && $foundNamespace != null) {
                $this->assertEquals(
                    $expectedNamespace,
                    $foundNamespace,
                    "Location of {$file} does not match formal namespace: {$expectedNamespace} found {$foundNamespace}\n"
                );
            }
        } else {
            $this->fail("Missing expected namespace \"{$expectedNamespace}\" for file: {$file}");
        }
    }
}
