<?php
/*
 * @author     The S Group <support@sashas.org>
 * @copyright  2021  Sashas IT Support Inc. (https://www.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */

declare(strict_types=1);

namespace TheSGroup\Cli;



use PHPUnit\TextUI\RuntimeException;

class Application
{

    private const VERSION = '1.1.8';

    public function run(array $argv): int
    {
        $this->printVersion();

        $path = pathinfo(\Phar::running(false));
        var_dump($path['dirname']);
        copy( MTF_PATH.'/unit/phpunit-cli.xml', $path['dirname'].'/phpunit-cli.xml');
        copy( MTF_PATH.'/unit/phpunit-cli.xml', $path['dirname'].'/phpunit-cli.xml');

        $args = [
            '-c',
            $path['dirname'].'/phpunit-cli.xml',
            '--log-junit',
            $path['dirname'].'/test-reports/junit.xml',
            '--coverage-html',
            $path['dirname'].'/test-coverage-html/',
            '--coverage-clover',
            $path['dirname'].'/clover.xml',
        ];


        var_dump(\Phar::running(false));

        ///mtf.phar/src/Cli/../../unit/phpunit.xml
        /// /src/Cli/../../unit/phpunit.xml
//        var_dump(__DIR__.'/../../unit/phpunit.xml');
//        var_dump(__DIR__);
//        var_dump(is_dir('/home/sashas/public_html/customerTwoFactorAuth/Test/'));
//        var_dump(is_dir('/home/sashas/public_html/customerTwoFactorAuth/mtf.phar/../Test'));
//        var_dump(is_dir('phar:///home/sashas/public_html/customerTwoFactorAuth/mtf.phar/../Test'));
//        \PHPUnit\TextUI\Command::run();
//        var_dump($argv);
$_SERVER['argv'] = $args;
        \PHPUnit\TextUI\Command::main();
//        try {
//            return (new static \PHPUnit\TextUI\Command)->run($argv);
//        } catch (Throwable $t) {
//            throw new RuntimeException(
//                $t->getMessage(),
//                (int) $t->getCode(),
//                $t
//            );
//        }
        return 0;
    }


    private function printVersion(): void
    {
        printf(
            'Magento Testing Framework %s by Alexander Lukyanov.' . PHP_EOL,
            self::VERSION
        );
    }

}