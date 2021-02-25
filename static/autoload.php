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

// phpcs:ignore Magento2.Security.IncludeFile.FoundIncludeFile
require_once __DIR__ . '/../../../autoload.php';
// phpcs:ignore Magento2.Security.IncludeFile.FoundIncludeFile
require BP . '/vendor/squizlabs/php_codesniffer/autoload.php';

$composerAutoloader = include __DIR__ . '/../../../autoload.php';
AutoloaderRegistry::registerAutoloader(new ClassLoaderWrapper($composerAutoloader));

$testsBaseDir = __DIR__ . '/../..';

$autoloadWrapper = \Magento\Framework\Autoload\AutoloaderRegistry::getAutoloader();
$autoloadWrapper->addPsr4(
    'Magento\\',
    [
        __DIR__ . '/integrity/testsuite/Magento/',
    ]
);