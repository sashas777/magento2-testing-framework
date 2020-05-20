# Magento 2 Testing Framework
[![Latest Stable Version](https://poser.pugx.org/thesgroup/magento2-testing-framework/v)](//packagist.org/packages/thesgroup/magento2-testing-framework) [![Total Downloads](https://poser.pugx.org/thesgroup/magento2-testing-framework/downloads)](//packagist.org/packages/thesgroup/magento2-testing-framework) [![Latest Unstable Version](https://poser.pugx.org/thesgroup/magento2-testing-framework/v/unstable)](//packagist.org/packages/thesgroup/magento2-testing-framework) [![License](https://poser.pugx.org/thesgroup/magento2-testing-framework/license)](//packagist.org/packages/thesgroup/magento2-testing-framework)

Magento 2 static/unit testing framework for single modules tests

## Installation
To use within your Magento 2 project you can use:

```bash
composer require --dev thesgroup/magento2-testing-framework
```

## Related Repositories
- [Docker Images](https://github.com/sashas777/magento-docker/)
- [Examples for pipeline configuration](https://github.com/sashas777/magento-docker-pipelines)
- [Magento Code Standard](https://github.com/magento/magento-coding-standard)
- [MFTF](https://github.com/magento/magento2-functional-testing-framework)

### Tests

#### PHPCS
Run PHP code style validation

```bash
vendor/bin/phpcs --config-set installed_paths vendor/magento/magento-coding-standard/
vendor/bin/phpcs --ignore=*/vendor/*,*/Test/*  --standard=Magento2 .
```

#### PHPMD
Run PHP mess detector validation

```bash
vendor/bin/phpmd . ansi vendor/thesgroup/magento2-testing-framework/static/phpmd/ruleset.xml --exclude vendor/,Test/
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

#### PHPUnit
Run unit tests and check for code coverage threshold.

```bash
vendor/bin/phpunit-tests
```
 
After execution following reports generated:
- JUnit log test-reports/junit.xml
- Html test coverage report test-coverage-html/
- Clover test coverage report clover.xml

### Integrity Tests
 
```bash
vendor/bin/phpunit -c vendor/thesgroup/magento2-testing-framework/static/integrity/phpunit.xml 
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

#### Phrase Tests
- Scan source code for detects invocations of outdated __() method.
- Scan source code for detects invocations of __() function or Phrase object, analyzes placeholders with arguments and see if they not equal.
- Will check if phrase is empty.

#### XML Tests
- xsi:noNamespaceSchemaLocation validation.
- XML DOM Validation.

### Environment Variables
Variable | Description | Default Value
------------ | -------------| -------------
TESTS_TEMP_DIR | Temporary directory for generated classes | ./tmp
UNIT_COVERAGE_THRESHOLD | Code coverage threshold | 70%

## Contribute to this module
Feel free to Fork and contribute to this module and create a pull request so we will merge your changes to master branch.

## Credits
Thanks the [the contributors](https://github.com/sashas777/magento2-testing-framework/graphs/contributors)