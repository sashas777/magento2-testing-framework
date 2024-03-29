<?php
/**
 * Scan source code for incorrect or undeclared modules dependencies
 * @author     The S Group <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (https://www.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */
namespace Magento\Test\Integrity;

use Magento\Framework\App\Utility\Files;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Exception\LocalizedException;
use Magento\TestFramework\Integrity\DeclarativeSchemaDependencyProvider;
use Magento\TestFramework\Dependency\DbRule;
use Magento\TestFramework\Dependency\DiRule;
use Magento\TestFramework\Dependency\LayoutRule;
use Magento\TestFramework\Dependency\PhpRule;
use Magento\TestFramework\Dependency\ReportsConfigRule;
use Magento\TestFramework\Dependency\AnalyticsConfigRule;
use Magento\TestFramework\Dependency\Route\RouteMapper;
use Magento\TestFramework\Dependency\VirtualType\VirtualTypeMapper;
use Magento\TestFramework\Integrity\DependencyProvider;
use \Magento\TestFramework\Integrity\GraphQlSchemaDependencyProvider;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DependencyTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Soft dependency between modules
     */
    const TYPE_SOFT = 'soft';

    /**
     * Hard dependency between modules
     */
    const TYPE_HARD = 'hard';

    /**
     * The identifier of dependency for mapping.
     */
    const MAP_TYPE_DECLARED = 'declared';

    /**
     * The identifier of dependency for mapping.
     */
    const MAP_TYPE_FOUND = 'found';

    /**
     * The identifier of dependency for mapping.
     */
    const MAP_TYPE_REDUNDANT = 'redundant';

    /**
     * Count of directories in path
     */
    const DIR_PATH_COUNT = 4;

    /**
     * List of config.xml files by modules
     *
     * Format: array(
     *  '{Module_Name}' => '{Filename}'
     * )
     *
     * @var array
     */
    protected static $_listConfigXml = [];

    /**
     * List of analytics.xml
     *
     * Format: array(
     *  '{Module_Name}' => '{Filename}'
     * )
     *
     * @var array
     */
    protected static $_listAnalyticsXml = [];

    /**
     * List of layout blocks
     *
     * Format: array(
     *  '{Area}' => array(
     *   '{Block_Name}' => array('{Module_Name}' => '{Module_Name}')
     * ))
     *
     * @var array
     */
    protected static $_mapLayoutBlocks = [];

    /**
     * List of layout handles
     *
     * Format: array(
     *  '{Area}' => array(
     *   '{Handle_Name}' => array('{Module_Name}' => '{Module_Name}')
     * ))
     *
     * @var array
     */
    protected static $_mapLayoutHandles = [];

    /**
     * List of dependencies
     *
     * Format: array(
     *  '{Module_Name}' => array(
     *   '{Type}' => array(
     *    'declared'  = array('{Dependency}', ...)
     *    'found'     = array('{Dependency}', ...)
     *    'redundant' = array('{Dependency}', ...)
     * )))
     * @var array
     */
    protected static $mapDependencies = [];

    /**
     * Regex pattern for validation file path of theme
     *
     * @var string
     */
    protected static $_defaultThemes = '';

    /**
     * Namespaces to analyze
     *
     * Format: {Namespace}|{Namespace}|...
     *
     * @var string
     */
    protected static $_namespaces;

    /**
     * Rule instances
     *
     * @var array
     */
    protected static $_rulesInstances = [];

    /**
     * White list for libraries
     *
     * @var array
     */
    private static $whiteList = [];

    /**
     * Routes whitelist
     *
     * @var array|null
     */
    private static $routesWhitelist = null;

    /**
     * @var RouteMapper
     */
    private static $routeMapper = null;

    /**
     * Sets up data
     *
     * @throws \Exception
     */
    public static function setUpBeforeClass(): void
    {
        $root = BP;
        $rootJson = json_decode(file_get_contents($root . '/composer.json'), true);
        if (preg_match('/magento\/project-*/', $rootJson['name']) == 1) {
            // The Dependency test is skipped for vendor/magento build
            self::markTestSkipped(
                'MAGETWO-43654: The build is running from vendor/magento. DependencyTest is skipped.'
            );
        }

        self::$routeMapper = new RouteMapper();
        self::$_namespaces = implode('|', Files::init()->getNamespaces());

        self::_prepareListConfigXml();
        self::_prepareListAnalyticsXml();

        self::_prepareMapLayoutBlocks();
        self::_prepareMapLayoutHandles();

        self::getLibraryWhiteLists();

        self::_initDependencies();
        self::_initThemes();
        self::_initRules();
    }

    /**
     * Initialize library white list
     */
    private static function getLibraryWhiteLists()
    {
        $componentRegistrar = new ComponentRegistrar();
        foreach ($componentRegistrar->getPaths(ComponentRegistrar::LIBRARY) as $library) {
            $library = str_replace('\\', '/', $library);
            if (strpos($library, 'Framework/') !== false) {
                $partOfLibraryPath = explode('/', $library);
                self::$whiteList[] = implode('\\', array_slice($partOfLibraryPath, -3));
            }
        }
    }

    /**
     * Initialize default themes
     */
    protected static function _initThemes()
    {
        $defaultThemes = [];
        foreach (self::$_listConfigXml as $file) {
            $config = simplexml_load_file($file);
            //phpcs:ignore Generic.PHP.NoSilencedErrors
            $nodes = @($config->xpath("/config/*/design/theme/full_name") ?: []);
            foreach ($nodes as $node) {
                $defaultThemes[] = (string)$node;
            }
        }
        self::$_defaultThemes = sprintf('#app/design.*/(%s)/.*#', implode('|', array_unique($defaultThemes)));
    }

    /**
     * Create rules objects
     *
     * @throws \Exception
     */
    protected static function _initRules()
    {
        $replaceFilePattern = str_replace('\\', '/', realpath(__DIR__))
            . '/_files/dependency_test/tables_*.php';
        $dbRuleTables = [];
        foreach (glob($replaceFilePattern) as $fileName) {
            //phpcs:ignore Magento2.Performance.ForeachArrayMerge
            $dbRuleTables = array_merge($dbRuleTables, include $fileName);
        }
        self::$_rulesInstances = [
            new PhpRule(
                self::$routeMapper->getRoutes(),
                self::$_mapLayoutBlocks,
                [],
                ['routes' => self::getRoutesWhitelist()]
            ),
            new DbRule($dbRuleTables),
            new LayoutRule(
                self::$routeMapper->getRoutes(),
                self::$_mapLayoutBlocks,
                self::$_mapLayoutHandles
            ),
            new DiRule(new VirtualTypeMapper()),
            new ReportsConfigRule($dbRuleTables),
            new AnalyticsConfigRule(),
        ];
    }

    /**
     * Initialize routes whitelist
     *
     * @return array
     */
    private static function getRoutesWhitelist(): array
    {
        if (is_null(self::$routesWhitelist)) {
            $routesWhitelistFilePattern = BP . '/Test/_files/dependency_test/whitelist/routes_*.php';
            $routesWhitelist = [];
            foreach (glob($routesWhitelistFilePattern) as $fileName) {
                //phpcs:ignore Magento2.Performance.ForeachArrayMerge
                $routesWhitelist = array_merge($routesWhitelist, include $fileName);
            }
            self::$routesWhitelist = $routesWhitelist;
        }
        return self::$routesWhitelist;
    }

    /**
     * Return cleaned file contents
     *
     * @param string $fileType
     * @param string $file
     * @return string
     */
    protected function _getCleanedFileContents($fileType, $file)
    {
        $contents = null;
        switch ($fileType) {
            case 'php':
                $contents = php_strip_whitespace($file);
                break;
            case 'layout':
            case 'config':
                //Removing xml comments
                $contents = preg_replace(
                    '~\<!\-\-/.*?\-\-\>~s',
                    '',
                    file_get_contents($file)
                );
                break;
            case 'template':
                $contents = php_strip_whitespace($file);
                //Removing html
                $contentsWithoutHtml = '';
                preg_replace_callback(
                    '~(<\?(php|=)\s+.*\?>)~sU',
                    function ($matches) use ($contents, &$contentsWithoutHtml) {
                        $contentsWithoutHtml .= $matches[1];
                        return $contents;
                    },
                    $contents
                );
                $contents = $contentsWithoutHtml;
                break;
            default:
                $contents = file_get_contents($file);
        }
        return $contents;
    }

    /**
     * @inheritdoc
     * @throws \Exception
     */
    public function testUndeclared()
    {
        $invoker = new \Magento\Test\CustomAggregateInvoker($this);
        $invoker(
            /**
             * Check undeclared modules dependencies for specified file
             *
             * @param string $fileType
             * @param string $file
             */
            function ($fileType, $file) {

                if (strpos($file, '/vendor/') || strpos($file, 'registration.php') || strpos($file, '/Test/')) {
                    return;
                }

                // Validates file when it is belonged to default themes
                $componentRegistrar = new ComponentRegistrar();
                foreach ($componentRegistrar->getPaths(ComponentRegistrar::THEME) as $themeDir) {
                    if (strpos($file, $themeDir . '/') !== false) {
                        return;
                    }
                }

                $foundModuleName = '';
                foreach ($componentRegistrar->getPaths(ComponentRegistrar::MODULE) as $moduleName => $moduleDir) {
                    if (strpos($file, $moduleDir . '/') !== false) {
                        $foundModuleName = str_replace('_', '\\', $moduleName);
                        break;
                    }
                }
                if (empty($foundModuleName)) {
                    return;
                }

                $module = $foundModuleName;
                $contents = $this->_getCleanedFileContents($fileType, $file);

                $dependencies = $this->getDependenciesFromFiles($module, $fileType, $file, $contents);

                // Collect dependencies
                $undeclaredDependency = $this->_collectDependencies($module, $dependencies);

                // Prepare output message
                $result = [];
                foreach ($undeclaredDependency as $type => $modules) {
                    $modules = array_unique($modules);
                    if (empty($modules)) {
                        continue;
                    }
                    $result[] = sprintf("%s [%s]", $type, implode(', ', $modules));
                }
                if (!empty($result)) {
                    $this->fail('Module ' . $module . ' has undeclared dependencies: ' . implode(', ', $result));
                }
            },
            $this->getAllFiles()
        );
    }

    /**
     * Retrieve dependencies from files
     *
     * @param string $module
     * @param string $fileType
     * @param string $file
     * @param string $contents
     * @return string[]
     * @throws LocalizedException
     */
    protected function getDependenciesFromFiles($module, $fileType, $file, $contents)
    {
        // Apply rules
        $dependencies = [];
        foreach (self::$_rulesInstances as $rule) {
            /** @var \Magento\TestFramework\Dependency\RuleInterface $rule */
            $newDependencies = $rule->getDependencyInfo($module, $fileType, $file, $contents);
            $dependencies[] = $newDependencies;
        }
        $dependencies = array_merge([], ...$dependencies);

        foreach ($dependencies as $dependencyKey => $dependency) {
            foreach (self::$whiteList as $namespace) {
                if (strpos($dependency['source'], $namespace) !== false) {
                    $dependency['modules'] = [$namespace];
                    $dependencies[$dependencyKey] = $dependency;
                }
            }
            $dependency['type'] = $dependency['type'] ?? 'type is unknown';
            if (empty($dependency['modules'])) {
                unset($dependencies[$dependencyKey]);
            }
        }

        return $dependencies;
    }

    /**
     * Collect dependencies
     *
     * @param string $currentModuleName
     * @param array $dependencies
     * @return array
     */
    protected function _collectDependencies($currentModuleName, $dependencies = [])
    {
        if (empty($dependencies)) {
            return [];
        }
        $undeclared = [];
        foreach ($dependencies as $dependency) {
            $this->collectDependency($dependency, $currentModuleName, $undeclared);
        }
        return $undeclared;
    }

    /**
     * Collect a dependency
     *
     * @param string $currentModule
     * @param array $dependency
     * @param array $undeclared
     */
    private function collectDependency($dependency, $currentModule, &$undeclared)
    {
        $type = isset($dependency['type']) ? $dependency['type'] : self::TYPE_HARD;

        $soft = $this->_getDependencies($currentModule, self::TYPE_SOFT, self::MAP_TYPE_DECLARED);
        $hard = $this->_getDependencies($currentModule, self::TYPE_HARD, self::MAP_TYPE_DECLARED);

        $declared = $type == self::TYPE_SOFT ? array_merge($soft, $hard) : $hard;

        $modules = $dependency['modules'];

        $this->collectConditionalDependencies($modules, $type, $currentModule, $declared, $undeclared);
    }

    /**
     * Collect non-strict dependencies when the module depends on one of modules
     *
     * @param array $conditionalDependencies
     * @param string $type
     * @param string $currentModule
     * @param array $declared
     * @param array $undeclared
     */
    private function collectConditionalDependencies(
        array $conditionalDependencies,
        string $type,
        string $currentModule,
        array $declared,
        array &$undeclared
    ) {
        array_walk(
            $conditionalDependencies,
            function (&$moduleName) {
                $moduleName = str_replace('_', '\\', $moduleName);
            }
        );
        $declaredDependencies = array_intersect($conditionalDependencies, $declared);

        foreach ($declaredDependencies as $moduleName) {
            if ($this->_isFake($moduleName)) {
                $this->_setDependencies($currentModule, $type, self::MAP_TYPE_REDUNDANT, $moduleName);
            }

            $this->addDependency($currentModule, $type, self::MAP_TYPE_FOUND, $moduleName);
        }

        if (empty($declaredDependencies)) {
            $undeclared[$type][] = implode(" || ", $conditionalDependencies);
        }
    }

    /**
     * Collect redundant dependencies
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @test
     * @depends testUndeclared
     * @throws \Exception
     */
    public function collectRedundant()
    {
        $schemaDependencyProvider = new DeclarativeSchemaDependencyProvider();

        foreach (array_keys(self::$mapDependencies) as $module) {
            $declared = $this->_getDependencies($module, self::TYPE_HARD, self::MAP_TYPE_DECLARED);
            //phpcs:ignore Magento2.Performance.ForeachArrayMerge
            $found = array_merge(
                $this->_getDependencies($module, self::TYPE_HARD, self::MAP_TYPE_FOUND),
                $this->_getDependencies($module, self::TYPE_SOFT, self::MAP_TYPE_FOUND),
                $schemaDependencyProvider->getDeclaredExistingModuleDependencies($module)
            );
            $found['Magento\Framework'] = 'Magento\Framework';
             //sashas
            if (array_key_exists('Magento\PageBuilder', $declared)) {
                unset($declared['Magento\PageBuilder']);
            }
            $this->_setDependencies($module, self::TYPE_HARD, self::MAP_TYPE_REDUNDANT, array_diff($declared, $found));
        }
    }

    /**
     * Check redundant dependencies
     *
     * @depends collectRedundant
     */
    public function testRedundant()
    {
        $output = [];
        //sashas
        $contents = file_get_contents(BP . '/composer.json');
        $composerJson = json_decode($contents, true);
        $composerModuleName = rtrim(array_key_first($composerJson['autoload']['psr-4']), '\\');

        foreach (array_keys(self::$mapDependencies) as $module) {
            if ($module !=$composerModuleName) {
                continue;
            }
            //sashas
            $result = [];
            $redundant = $this->_getDependencies($module, self::TYPE_HARD, self::MAP_TYPE_REDUNDANT);
            if (!empty($redundant)) {
                $result[] = sprintf(
                    "\r\nModule %s: %s [%s]",
                    $module,
                    self::TYPE_HARD,
                    implode(', ', array_values($redundant))
                );
            }

            if (!empty($result)) {
                $output[] = implode(', ', $result);
            }
        }
        if (!empty($output)) {
            $this->fail("Redundant dependencies found!\r\n" . implode(' ', $output));
        }
    }

    /**
     * Convert file list to data provider structure
     *
     * @param string $fileType
     * @param array $files
     * @param bool|null $skip
     * @return array
     */
    protected function _prepareFiles($fileType, $files, $skip = null)
    {
        $result = [];
        foreach ($files as $relativePath => $file) {
            $absolutePath = $file[0];
            if (!$skip && substr_count($relativePath, '/') < self::DIR_PATH_COUNT) {
                continue;
            }
            $result[$relativePath] = [$fileType, $absolutePath];
        }
        return $result;
    }

    /**
     * Return all files
     *
     * @return array
     * @throws \Exception
     */
    public function getAllFiles()
    {
        return array_merge(
            $this->_prepareFiles(
                'php',
                Files::init()->getPhpFiles(Files::INCLUDE_APP_CODE | Files::AS_DATA_SET | Files::INCLUDE_NON_CLASSES),
                true
            ),
            $this->_prepareFiles('config', Files::init()->getConfigFiles()),
            $this->_prepareFiles('layout', Files::init()->getLayoutFiles()),
            $this->_prepareFiles('template', Files::init()->getPhtmlFiles())
        );
    }

    /**
     * Prepare list of config.xml files (by modules).
     *
     * @throws \Exception
     */
    protected static function _prepareListConfigXml()
    {
        $files = Files::init()->getConfigFiles('config.xml', [], false);
        foreach ($files as $file) {
            if (preg_match('/(?<namespace>[A-Z][a-z]+)[_\/\\\\](?<module>[A-Z][a-zA-Z]+)/', $file, $matches)) {
                $module = $matches['namespace'] . '\\' . $matches['module'];
                self::$_listConfigXml[$module] = $file;
            }
        }
    }

    /**
     * Prepare list of analytics.xml files
     *
     * @throws \Exception
     */
    protected static function _prepareListAnalyticsXml()
    {
        $files = Files::init()->getDbSchemaFiles('analytics.xml', [], false);
        foreach ($files as $file) {
            if (preg_match('/(?<namespace>[A-Z][a-z]+)[_\/\\\\](?<module>[A-Z][a-zA-Z]+)/', $file, $matches)) {
                $module = $matches['namespace'] . '\\' . $matches['module'];
                self::$_listAnalyticsXml[$module] = $file;
            }
        }
    }

    /**
     * Prepare map of layout blocks
     *
     * @throws \Exception
     */
    protected static function _prepareMapLayoutBlocks()
    {
        $files = Files::init()->getLayoutFiles([], false);
        foreach ($files as $file) {
            $area = 'default';
            if (preg_match('/[\/](?<area>adminhtml|frontend)[\/]/', $file, $matches)) {
                $area = $matches['area'];
                self::$_mapLayoutBlocks[$area] = self::$_mapLayoutBlocks[$area] ?? [];
            }
            if (preg_match('/(?<namespace>[A-Z][a-z]+)[_\/\\\\](?<module>[A-Z][a-zA-Z]+)/', $file, $matches)) {
                $module = $matches['namespace'] . '\\' . $matches['module'];
                $xml = simplexml_load_file($file);
                foreach ((array)$xml->xpath('//container | //block') as $element) {
                    /** @var \SimpleXMLElement $element */
                    $attributes = $element->attributes();
                    $block = (string)$attributes->name;
                    if (!empty($block)) {
                        self::$_mapLayoutBlocks[$area][$block] = self::$_mapLayoutBlocks[$area][$block] ?? [];
                        self::$_mapLayoutBlocks[$area][$block][$module] = $module;
                    }
                }
            }
        }
    }

    /**
     * Prepare map of layout handles
     *
     * @throws \Exception
     */
    protected static function _prepareMapLayoutHandles()
    {
        $files = Files::init()->getLayoutFiles([], false);
        foreach ($files as $file) {
            $area = 'default';
            if (preg_match('/\/(?<area>adminhtml|frontend)\//', $file, $matches)) {
                $area = $matches['area'];
                self::$_mapLayoutHandles[$area] = self::$_mapLayoutHandles[$area] ?? [];
            }
            if (preg_match('/app\/code\/(?<namespace>[A-Z][a-z]+)[_\/\\\\](?<module>[A-Z][a-zA-Z]+)/', $file, $matches)
            ) {
                $module = $matches['namespace'] . '\\' . $matches['module'];
                $xml = simplexml_load_file($file);
                foreach ((array)$xml->xpath('/layout/child::*') as $element) {
                    /** @var \SimpleXMLElement $element */
                    $handle = $element->getName();
                    self::$_mapLayoutHandles[$area][$handle] = self::$_mapLayoutHandles[$area][$handle] ?? [];
                    self::$_mapLayoutHandles[$area][$handle][$module] = $module;
                }
            }
        }
    }

    /**
     * Retrieve dependency types array
     *
     * @return array
     */
    protected static function _getTypes()
    {
        return [self::TYPE_HARD, self::TYPE_SOFT];
    }

    /**
     * Converts a composer json component name into the Magento Module form
     *
     * @param string $jsonName The name of a composer json component or dependency e.g. 'magento/module-theme'
     * @param array $packageModuleMap Mapping package name with module namespace.
     * @return string The corresponding Magento Module e.g. 'Magento\Theme'
     */
    protected static function convertModuleName(string $jsonName, array $packageModuleMap): string
    {
        if (isset($packageModuleMap[$jsonName])) {
            return $packageModuleMap[$jsonName];
        }

        if (strpos($jsonName, 'magento/magento') !== false || strpos($jsonName, 'magento/framework') !== false) {
            $moduleName = str_replace('/', "\t", $jsonName);
            $moduleName = str_replace('framework-', "Framework\t", $moduleName);
            $moduleName = str_replace('-', ' ', $moduleName);
            $moduleName = ucwords($moduleName);
            $moduleName = str_replace("\t", '\\', $moduleName);
            $moduleName = str_replace(' ', '', $moduleName);

            return $moduleName;
        }

        return $jsonName;
    }

    /**
     * Initialise map of dependencies.
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @throws \Exception
     */
    protected static function _initDependencies()
    {
        $packageModuleMap = self::getPackageModuleMapping();
        $jsonFiles = Files::init()->getComposerFiles(ComponentRegistrar::MODULE, false);
        foreach ($jsonFiles as $file) {
            $contents = file_get_contents($file);
            $decodedJson = json_decode($contents);
            if (null == $decodedJson) {
                //phpcs:ignore Magento2.Exceptions.DirectThrow
                throw new \Exception("Invalid Json: $file");
            }
            $json = new \Magento\Framework\Config\Composer\Package(json_decode($contents));
            $moduleName = self::convertModuleName($json->get('name'), $packageModuleMap);
            if (!isset(self::$mapDependencies[$moduleName])) {
                self::$mapDependencies[$moduleName] = [];
            }
            foreach (self::_getTypes() as $type) {
                if (!isset(self::$mapDependencies[$moduleName][$type])) {
                    self::$mapDependencies[$moduleName][$type] = [
                        self::MAP_TYPE_DECLARED  => [],
                        self::MAP_TYPE_FOUND     => [],
                        self::MAP_TYPE_REDUNDANT => [],
                    ];
                }
            }

            $require = array_keys((array)$json->get('require'));
            self::addDependencies($moduleName, $require, self::TYPE_HARD, $packageModuleMap);

            $suggest = array_keys((array)$json->get('suggest'));
            self::addDependencies($moduleName, $suggest, self::TYPE_SOFT, $packageModuleMap);
        }
    }

    /**
     * Add dependencies to dependency list.
     *
     * @param string $moduleName
     * @param array $packageNames
     * @param string $type
     * @param array $packageModuleMap
     *
     * @return void
     */
    private static function addDependencies(
        string $moduleName,
        array $packageNames,
        string $type,
        array $packageModuleMap
    ): void {
        $packageNames = array_filter(
            $packageNames,
            function ($packageName) use ($packageModuleMap) {
                return isset($packageModuleMap[$packageName]) ||
                    0 === strpos($packageName, 'magento/')
                    && 'magento/magento-composer-installer' != $packageName;
            }
        );

        foreach ($packageNames as $packageName) {
            self::addDependency(
                $moduleName,
                $type,
                self::MAP_TYPE_DECLARED,
                self::convertModuleName($packageName, $packageModuleMap)
            );
        }
    }

    /**
     * Add dependency map items.
     *
     * @param string $module
     * @param string $type
     * @param string $mapType
     * @param string $dependency
     *
     * @return void
     */
    private static function addDependency(string $module, string $type, string $mapType, string $dependency): void
    {
        if (isset(self::$mapDependencies[$module][$type][$mapType])) {
            self::$mapDependencies[$module][$type][$mapType][$dependency] = $dependency;
        }
    }

    /**
     * Returns package name on module name mapping.
     *
     * @return array
     * @throws \Exception
     */
    private static function getPackageModuleMapping(): array
    {
        $jsonFiles = Files::init()->getComposerFiles(ComponentRegistrar::MODULE, false);

        $packageModuleMapping = [];
        foreach ($jsonFiles as $file) {
            if (strpos($file, '/dev/tests/')) {
                continue;
            }
            if (strpos($file, '/Test/Unit/')) {
                continue;
            }
            $contents = file_get_contents($file);
            $composerJson = json_decode($contents, true);
            if (null == $composerJson) {
                //phpcs:ignore Magento2.Exceptions.DirectThrow
                throw new \Exception("Invalid Json: $file");
            }

            //sashas
            if (!isset($composerJson['type']) || !in_array($composerJson['type'], ['magento2-module'])) {
                continue;
            }
            //sashas
            $moduleConfigFile = dirname($file) . 'etc/module.xml';
            if (!file_exists($moduleConfigFile) && isset($composerJson['autoload']) && isset($composerJson['autoload']['psr-4']) ) {
                $moduleConfigFile = dirname($file) .'/'. array_shift($composerJson['autoload']['psr-4']). '/etc/module.xml';
                //in case psr4 used
            }
            if (!file_exists($moduleConfigFile)) {
                continue;
            }
            $moduleXml = simplexml_load_file($moduleConfigFile);
            $moduleName = str_replace('_', '\\', (string)$moduleXml->module->attributes()->name);
            $packageName = $composerJson['name'];
            $packageModuleMapping[$packageName] = $moduleName;
        }

        return $packageModuleMapping;
    }

    /**
     * Retrieve array of dependency items
     *
     * @param $module
     * @param $type
     * @param $mapType
     * @return array
     */
    protected function _getDependencies($module, $type, $mapType)
    {
        if (isset(self::$mapDependencies[$module][$type][$mapType])) {
            return self::$mapDependencies[$module][$type][$mapType];
        }

        return [];
    }

    /**
     * Set dependency map items
     *
     * @param $module
     * @param $type
     * @param $mapType
     * @param $dependencies
     */
    protected function _setDependencies($module, $type, $mapType, $dependencies)
    {
        if (!is_array($dependencies)) {
            $dependencies = [$dependencies];
        }
        if (isset(self::$mapDependencies[$module][$type][$mapType])) {
            self::$mapDependencies[$module][$type][$mapType] = $dependencies;
        }
    }

    /**
     * Check if module is fake
     *
     * @param $module
     * @return bool
     */
    protected function _isFake($module)
    {
        return isset(self::$mapDependencies[$module]) ? false : true;
    }
}
