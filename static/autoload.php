<?php
/**
 * @author     The S Group <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (https://www.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Autoload\AutoloaderRegistry;
use Magento\Framework\Autoload\ClassLoaderWrapper;

//phpcs:ignore Magento2.Functions.DiscouragedFunction
define('BP', realpath(__DIR__ . '/../../../../'));
//$baseDir = realpath(__DIR__ . '/../../../../');
// phpcs:ignore Magento2.Security.IncludeFile.FoundIncludeFile
require_once __DIR__ . '/../../../autoload.php';
// phpcs:ignore Magento2.Security.IncludeFile.FoundIncludeFile
//require __DIR__ . '/../../../squizlabs/php_codesniffer/autoload.php';
//$testsBaseDir = __DIR__ . '/static';


$composerAutoloader = include __DIR__ . '/../../../autoload.php';
AutoloaderRegistry::registerAutoloader(new ClassLoaderWrapper($composerAutoloader));

$autoloadWrapper = AutoloaderRegistry::getAutoloader();
//$autoloadWrapper->addPsr4(
//    'Magento\\',
//    [
//        $testsBaseDir . '/testsuite/Magento/',
//        $testsBaseDir . '/framework/Magento/',
//        $testsBaseDir . '/framework/tests/unit/testsuite/Magento',
//    ]
//);
//$autoloadWrapper->addPsr4(
//    'Magento\\TestFramework\\',
//    [
//        $testsBaseDir . '/framework/Magento/TestFramework/'
//    ]
//);
//$autoloadWrapper->addPsr4('Magento\\CodeMessDetector\\', $testsBaseDir . '/framework/Magento/CodeMessDetector');

//$generatedCode = DirectoryList::getDefaultConfig()[DirectoryList::GENERATED_CODE][DirectoryList::PATH];
//$autoloadWrapper->addPsr4('Magento\\', $baseDir . '/' . $generatedCode . '/Magento/');
