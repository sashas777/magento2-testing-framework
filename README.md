## Magento 2 Testing Framework
Magento 2 static/unit testing framework for single modules tests

## Installation
To use within your Magento 2 project you can use:

```bash
composer require --dev thesgroup/magento2-testing-framework
```

## Related Repositories
- [Docker Images](https://github.com/sashas777/magento-docker/)
- [Examples for pipeline configuration](https://github.com/sashas777/magento-docker-pipelines)

## Usage

```bash
vendor/bin/phpunit-tests
```
Runs unit tests and checks for code coverage treshold

## PHPCS
[Magento Conding Standard](https://github.com/magento/magento-coding-standard)

## PHPMD


## PHPUnit

#### Environment Variables
Variable | Description
------------ | -------------
TESTS_TEMP_DIR | Temporary directory for generated classes
UNIT_COVERAGE_THRESHOLD | Code coverage treshold
