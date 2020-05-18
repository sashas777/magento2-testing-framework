<?php
/**
 * @author     The S Group <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (https://www.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */

use \Magento\Framework\App\Filesystem\DirectoryList;

//phpcs:ignore Magento2.Functions.DiscouragedFunction
$baseDir = realpath(__DIR__ . '/../../../../');
// phpcs:ignore Magento2.Security.IncludeFile.FoundIncludeFile
require $baseDir . '/vendor/autoload.php';
// phpcs:ignore Magento2.Security.IncludeFile.FoundIncludeFile
require $baseDir . '/vendor/squizlabs/php_codesniffer/autoload.php';
$testsBaseDir = __DIR__ . '/static';
$autoloadWrapper = \Magento\Framework\Autoload\AutoloaderRegistry::getAutoloader();
//$autoloadWrapper->addPsr4(
//    'Magento\\',
//    [
//        $testsBaseDir . '/testsuite/Magento/',
//        $testsBaseDir . '/framework/Magento/',
//        $testsBaseDir . '/framework/tests/unit/testsuite/Magento',
//    ]
//);
$autoloadWrapper->addPsr4(
    'Magento\\TestFramework\\',
    [
        $testsBaseDir . '/framework/Magento/TestFramework/'
    ]
);
//$autoloadWrapper->addPsr4('Magento\\CodeMessDetector\\', $testsBaseDir . '/framework/Magento/CodeMessDetector');

//$generatedCode = DirectoryList::getDefaultConfig()[DirectoryList::GENERATED_CODE][DirectoryList::PATH];
//$autoloadWrapper->addPsr4('Magento\\', $baseDir . '/' . $generatedCode . '/Magento/');
