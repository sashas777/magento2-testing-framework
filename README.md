# Magento 2 Testing Framework
[![Latest Stable Version](https://poser.pugx.org/thesgroup/magento2-testing-framework/v)](//packagist.org/packages/thesgroup/magento2-testing-framework) [![Total Downloads](https://poser.pugx.org/thesgroup/magento2-testing-framework/downloads)](//packagist.org/packages/thesgroup/magento2-testing-framework) [![Latest Unstable Version](https://poser.pugx.org/thesgroup/magento2-testing-framework/v/unstable)](//packagist.org/packages/thesgroup/magento2-testing-framework) [![License](https://poser.pugx.org/thesgroup/magento2-testing-framework/license)](//packagist.org/packages/thesgroup/magento2-testing-framework)

Magento 2 static/unit testing framework for a single module tests. It can be used for a pipeline or to  verify a Magento 2 module.

## Installation
Run the following command on your Magento 2 project:

```bash
composer require --dev thesgroup/magento2-testing-framework
```

***
## Pipeline Examples
Assumes $MAGENTO_USER and $MAGENTO_PASS set as pipeline variables.
- [Bitbucket pipelines](https://github.com/sashas777/magento2-testing-framework/blob/master/bitbucket-pipelines.yml.sample)
- [Gitlab CI](https://github.com/sashas777/magento2-testing-framework/blob/master/.gitlab-ci.yml.sample)
***

## Basic Usage
In the most cases you may use basic test suite to run PHP code, JavaScript, PhpUnit tests.

### PHP Code
There is an option to run all php tests using one command.
It runs following tests:
- PHP Coding Standard Verification
- Code Integrity Tests
- HTML Static Code Analysis
- Less Static Code Analysis
- GraphQL Static Code Analysis

You can run all tests by using the command:
```bash
vendor/bin/phpcs --config-set installed_paths vendor/magento/magento-coding-standard,vendor/phpcompatibility/php-compatibility/PHPCompatibility
vendor/bin/run-all-tests
```

When you need to delete test files you can use the cleanup command:
```bash
vendor/bin/cleanup
```
### PHPUnit
Run unit tests and check for code coverage threshold.

```bash
vendor/bin/phpunit-tests
```

After execution following reports generated:
- JUnit log test-reports/junit.xml
- Html test coverage report test-coverage-html/
- Clover test coverage report clover.xml

To set code coverage threshold 80% (The default value 70%):
```bash
vendor/bin/phpunit-tests 80
```
### Javascript
Run ESLint to ensure the quality of your JavaScript code:
```bash
vendor/bin/js-tests
```
Fix ESLint Locally:
```bash
npm install eslint --save-dev
npx eslint -c vendor/thesgroup/magento2-testing-framework/static/js/eslint/.eslintrc --ignore-pattern=vendor/** --no-error-on-unmatched-pattern .
```
 
***

## Advanced Usage

### PHP Coding Standard Verification
These testsuite include: PHPCS, PHPMD, PHPCPD, PHPStan Tests and strict types declaration.
You can run it exclusively using the following command:
```bash
vendor/bin/phpcs --config-set installed_paths vendor/magento/magento-coding-standard,vendor/phpcompatibility/php-compatibility/PHPCompatibility
vendor/bin/phpunit --testsuite="PHP Coding Standard Verification" -c vendor/thesgroup/magento2-testing-framework/static/integrity/phpunit.xml
```
### Code Integrity Tests
You can run it exclusively using the following command:
```bash
vendor/bin/phpunit --testsuite="Code Integrity Tests" -c vendor/thesgroup/magento2-testing-framework/static/integrity/phpunit.xml
```

### HTML Static Code Analysis
You can run it exclusively using the following command:
```bash
vendor/bin/phpunit --testsuite="HTML Static Code Analysis" -c vendor/thesgroup/magento2-testing-framework/static/integrity/phpunit.xml
```

### Less Static Code Analysis
You can run it exclusively using the following command:
```bash
vendor/bin/phpunit --testsuite="Less Static Code Analysis" -c vendor/thesgroup/magento2-testing-framework/static/integrity/phpunit.xml
```

### GraphQL Static Code Analysis
You can run it exclusively using the following command:
```bash
vendor/bin/phpunit --testsuite="GraphQL Static Code Analysis" -c vendor/thesgroup/magento2-testing-framework/static/integrity/phpunit.xml
```

## PHP Code Beautifier and Fixer (PHPCBF)
To automatically fix as many sniff violations as possible, use the phpcbf command:
```bash
vendor/bin/phpcs --config-set installed_paths vendor/magento/magento-coding-standard/,vendor/phpcompatibility/php-compatibility/PHPCompatibility
vendor/bin/phpcbf --standard=Magento2 .
```

## Ignoring Files and Folders
Specific files/folders can be added to blacklist at the module folder by adding <globPattern> at new line

#### PHPCS
```bash
{module_dir}/Test/_files/phpcs/ignorelist/*.txt 
```
#### PHPMD
```bash
{module_dir}/Test/_files/phpmd/ignorelist/*.txt
```
#### PHPCPD
```bash
{module_dir}/Test/_files/phpcpd/blacklist/*.txt
```
#### Strict Type Declarations
```bash
{module_dir}/Test/_files/blacklist/strict_type.txt
```
#### PHPStan
```bash
{module_dir}/Test/_files/phpstan/blacklist/*.txt
```
  
***

## Information About Tests
## Magento Specific Rules
###### AllPurposeAction
Controllers (classes implementing ActionInterface) have to implement marker Http<Method>ActionInterface
to restrict incoming requests by methods.

###### CookieAndSessionMisuse
Sessions and cookies must only be used in classes directly responsible for HTML presentation because Web APIs do not
rely on cookies and sessions. If you need to get current user use Magento\Authorization\Model\UserContextInterface

###### FinalImplementation
Final keyword is prohibited in Magento as this decreases extensibility and customizability.
Final classes and method are not compatible with plugins and proxies.

## Code Integrity Tests
 
The command above will perform following tests:

- Compiler test. Check compilation of DI definitions and code generation
- Test block names exists
- Test layout declaration and usage of block elements
- Test format of layout files
- Test layout declaration and usage of block elements
- Test declarations of handles in theme layout updates
- Test ACL in the admin area by various assertions.
- Find adminhtml/system.xml files and validate them.
- Find fieldset.xml files and validate them.
- Check interfaces inherited from \Magento\Framework\Api\ExtensibleDataInterface.
- Find webapi xml files and validate them.
- Find widget.xml files and validate them.
- Scan source code for references to classes and see if they indeed exist.
- A test that enforces validity of composer.json files and any other conventions in Magento components.
- Validates information on the dependency between the modules according to the declarative schema.
- Oberver Implementation. (PAY ATTENTION: Current implementation does not support of virtual types)
- Scan source code for incorrect or undeclared modules dependencies.
- Check Magento modules structure for circular dependencies
- Verify whether all payment methods are declared in appropriate modules
- Tests to find obsolete install/upgrade schema/data scripts.
- Coverage of obsolete nodes in layout
- Static test for phtml template files.
- Less Static Code Analysis
- GraphQL Static Code Analysis
- Scan source code for detects invocations of outdated __() method.
- Scan source code for detects invocations of __() function or Phrase object, analyzes placeholders with arguments and see if they not equal.
- Will check if phrase is empty.
- xsi:noNamespaceSchemaLocation validation.
- XML DOM Validation.
 
## Contribute To The Module
Feel free to Fork and contribute to this module and create a pull request so we will merge your changes to master branch.

## Credits
Thanks the [the contributors](https://github.com/sashas777/magento2-testing-framework/graphs/contributors)

## Related Resources
- [Docker Images](https://github.com/sashas777/magento-docker/)
- [Examples for pipeline configuration](https://github.com/sashas777/magento-docker-pipelines)
- [Magento Code Standard](https://github.com/magento/magento-coding-standard)
- [MFTF](https://github.com/magento/magento2-functional-testing-framework)
- [Magento Coding Standard Severity](https://github.com/magento/magento-coding-standard/blob/v5/Magento2/ruleset.xml)
- [PHPStan Output Format](https://phpstan.org/user-guide/output-format)