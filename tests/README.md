Running tests
=============

You can run tests in all supported PHP versions using [PHPUnit](https://phpunit.de). Follow the following tests to run all the tests:

1. Install [PHPUnit](https://phpunit.de) using [Composer](http://getcomposer.org).

```
composer.phar install --dev
```

2. Open [phpunit.xml.dist](https://github.com/sony/cdn-purge-control-php/blob/master/phpunit.xml.dist) and specify all the configuration & credentials.

3. Run all the tests with coverage.

```
make coverage
```

Once completed, test report along with coverage results can be found in ```test-results/report.xml``` & ```coverage/index.html``` in the [build](https://github.com/sony/cdn-purge-control-php/tree/master/build) directory respectively.
