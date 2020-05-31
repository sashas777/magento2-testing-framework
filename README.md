# Magento 2 Testing Framework
[![Latest Stable Version](https://poser.pugx.org/thesgroup/magento2-testing-framework/v)](//packagist.org/packages/thesgroup/magento2-testing-framework) [![Total Downloads](https://poser.pugx.org/thesgroup/magento2-testing-framework/downloads)](//packagist.org/packages/thesgroup/magento2-testing-framework) [![Latest Unstable Version](https://poser.pugx.org/thesgroup/magento2-testing-framework/v/unstable)](//packagist.org/packages/thesgroup/magento2-testing-framework) [![License](https://poser.pugx.org/thesgroup/magento2-testing-framework/license)](//packagist.org/packages/thesgroup/magento2-testing-framework)

Magento 2 static/unit testing framework for single modules tests

## Installation
To use within your Magento 2 project you can use:

```bash
composer require --dev thesgroup/magento2-testing-framework
```

## Summary
- [PHPCS](https://github.com/sashas777/magento2-testing-framework#phpcs)
- [PHPMD](https://github.com/sashas777/magento2-testing-framework#phpmd)
- [PHPStan](https://github.com/sashas777/magento2-testing-framework#phpstan---php-static-analysis-tool)
- [PHPUnit](https://github.com/sashas777/magento2-testing-framework#phpunit)
- [Javascript Tests](https://github.com/sashas777/magento2-testing-framework#javascript-tests)
- [Integrity Tests](https://github.com/sashas777/magento2-testing-framework#integrity-tests)

## Tests

#### PHPCS
Run PHP code style validation

```bash
vendor/bin/phpcs-tests
```
By default, PHP_CodeSniffer assigns a severity of 5 to all errors and warnings. PHP_CodeSniffer allows you to decide what the minimum severity level must be to show a message in its report using the `--severity` command line argument.
To hide errors and warnings with a severity less than 3:
```bash
vendor/bin/phpcs-tests 3
```

Also you can run phpcbf from the command-line to fix your code:
```bash
vendor/bin/phpcbf --standard=Magento2 .
```
  
#### PHPMD
Run PHP mess detector validation

```bash
vendor/bin/phpmd-tests
```

##### Magento Specific Rules
###### AllPurposeAction
Controllers (classes implementing ActionInterface) have to implement marker Http<Method>ActionInterface
to restrict incoming requests by methods.

###### CookieAndSessionMisuse
Sessions and cookies must only be used in classes directly responsible for HTML presentation because Web APIs do not
rely on cookies and sessions. If you need to get current user use Magento\Authorization\Model\UserContextInterface

###### FinalImplementation
Final keyword is prohibited in Magento as this decreases extensibility and customizability.
Final classes and method are not compatible with plugins and proxies.

#### PHPStan - PHP Static Analysis Tool
Run PHPStan static analysis

```bash
vendor/bin/phpstan-tests
```

You can specify optional parameters rule level and outpout format:

```bash
vendor/bin/phpstan-tests 1 table
```

#### PHPUnit
Run unit tests and check for code coverage threshold.

```bash
vendor/bin/phpunit-tests
```
 
After execution following reports generated:
- JUnit log test-reports/junit.xml
- Html test coverage report test-coverage-html/
- Clover test coverage report clover.xml
 
To set code coverage threshold 80% (Default 70%):
```bash
vendor/bin/phpunit-tests 80
```
### Javascript Tests
Run ESLint to ensure the quality of your JavaScript code:
```bash
vendor/bin/js-tests
```
Fix ESLint Locally (You should have eslint installed):
```bash
npx eslint -c vendor/thesgroup/magento2-testing-framework/static/js/eslint/.eslintrc --ignore-pattern=vendor/** . --fix
```

### Integrity Tests
 
```bash
vendor/bin/integrity-tests
```
The command above will perform following tests:

#### DI Compiler Test
Compiler test. Check compilation of DI definitions and code generation

#### Layout Tests
- Test block names exists
- Test layout declaration and usage of block elements
- Test format of layout files
- Test layout declaration and usage of block elements
- Test declarations of handles in theme layout updates

#### Magento Tests
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

#### Phrase Tests
- Scan source code for detects invocations of outdated __() method.
- Scan source code for detects invocations of __() function or Phrase object, analyzes placeholders with arguments and see if they not equal.
- Will check if phrase is empty.

#### XML Tests
- xsi:noNamespaceSchemaLocation validation.
- XML DOM Validation.

## Contribute to this module
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