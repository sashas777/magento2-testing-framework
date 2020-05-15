# Magento 2 Testing Framework
Magento 2 static/unit testing framework for single modules tests

## Installation
To use within your Magento 2 project you can use:

```bash
composer require --dev thesgroup/magento2-testing-framework
```

## Related Repositories
- [Docker Images](https://github.com/sashas777/magento-docker/)
- [Examples for pipeline configuration](https://github.com/sashas777/magento-docker-pipelines)

## PHPCS
[Resource MCS](https://github.com/magento/magento-coding-standard)

```bash
vendor/bin/phpcs --config-set installed_paths vendor/magento/magento-coding-standard/
vendor/bin/phpcs --ignore=*/vendor/*,*/Test/*  --standard=Magento2 .
```
Run PHP code style validation

## PHPMD
[Resource MFTF](https://github.com/magento/magento2-functional-testing-framework)

```bash
vendor/bin/phpmd . ansi vendor/thesgroup/magento2-testing-framework/static/phpmd/ruleset.xml --exclude vendor/,Test/
```

Run PHP mess detector validation

### Magento Specific Rules
#### AllPurposeAction
Controllers (classes implementing ActionInterface) have to implement marker Http<Method>ActionInterface
to restrict incoming requests by methods.

#### CookieAndSessionMisuse
Sessions and cookies must only be used in classes directly responsible for HTML presentation because Web APIs do not
rely on cookies and sessions. If you need to get current user use Magento\Authorization\Model\UserContextInterface

#### FinalImplementation
Final keyword is prohibited in Magento as this decreases extensibility and customizability.
Final classes and method are not compatible with plugins and proxies.

## PHPUnit

```bash
vendor/bin/phpunit-tests
```
Run unit tests and check for code coverage treshold.
After execution following reports generated:
- JUnit log test-reports/junit.xml
- Html test coverage report test-coverage-html/
- Clover test coverage report clover.xml

#### Environment Variables
Variable | Description
------------ | -------------
TESTS_TEMP_DIR | Temporary directory for generated classes
UNIT_COVERAGE_THRESHOLD | Code coverage treshold
