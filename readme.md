# Laravel Utilities

This package is intended to be a collection of helpful utilities to assist with the development and maintenance of Laravel Applications.

| Laravel Version  | Package Version  | Packagist Branch |
|---|---|---|
| 7.*  | 10.*  | ```"srlabs/laravel-testing-utilities": "~10"```   |
| 8.*  | 11.*  | ```"srlabs/laravel-testing-utilities": "~11"```   |
| 9.*  | 12.*  | ```"srlabs/laravel-testing-utilities": "~12"```   |

To install this package, run
```bash
$ composer require srlabs/laravel-testing-utilities
```

and then add the service provider to your service providers listing in ```app/config/app.php```

```php
'providers' => [
    // ...
    'SRLabs\Utilities\UtilitiesServiceProvider'
    // ...
],
```

## Testing Assistant

This utility makes it easy to implement the testing stratgey described by [Chris Duell](https://github.com/duellsy) in his blog post *[Speeding up PHP unit tests 15 times](http://www.chrisduell.com/blog/development/speeding-up-unit-tests-in-php/)*.  When running tests that require the use of a database persistence layer, running migrations and seeding the database for each test can take a very long time.  Chris instead suggests creating a sqlite database file ahead of time, and making a new copy of that file for each test instead.

This package provides an artisan command ('utility:testdb') that will run your migrations and seeds and save them to a pre-defined sqlite file.  Once that is complete, there is a companion trait that you add to your tests which will copy the staging database file to the testing database location when running tests.

You need to define a sqlite database connection in your ```config/database.php``` file.  The connection name can be whatever you would like, but the package will assume a name of 'staging' if you don't provide one.

Default 'staging' connection:

```bash
$ php artisan utility:testdb
```

Custom 'sqlite_testing' connection:

```bash
$ php artisan utility:testdb sqlite_testing
```

You can also specify a Database Seeder class:

```bash
$ php artisan utility:testdb sqlite_testing --class="SentinelDatabaseSeeder"
```

This command will migrate and seed the sqlite database you have specified.

When you are ready to use this new sqlite file in your tests, add the ```TestingDatabase``` trait to your test class, and use it as such:

```php

use SRLabs\Utilities\Traits\TestingDatabaseTrait;

class FooTest extends TestCase {

    use TestingDatabaseTrait;

    public function setUp()
    {
      parent::setUp();

      $this->prepareDatabase('staging', 'testing');
    }

    public function testSomethingIsTrue()
    {
        $this->assertTrue(true);
    }

}
```
In this example "staging" is the Database connection that represents the pre-compiled sqlite file, and "testing" is a separate connection that represents the database that will be used by the tests. Each time the ```setUp()``` method is called, the testing sqlite file will be replaced by the staging sqlite file, effectively resetting your database to a clean starting point.
You may need to do some extra configuration to have phpunit use your "testing" database.

## (Optional) Run Automatically When Running PHPUnit

Using this method will have `artisan utility:testdb` execute before any tests are ran **only** if there are new migration changes.

#### 1. Create file `bootstrap/testing.php`:

```php
<?php

define('ARTISAN_PATH', realpath(__DIR__ . '/../artisan'));

/*
|--------------------------------------------------------------------------
| Check for new migrations and reseed if necessary
|--------------------------------------------------------------------------
|
*/

passthru('(php '.ARTISAN_PATH.' migrate:status --database='.(getenv('DB_CONNECTION') ?: 'testing').' | grep -q "| N    |") && php '.ARTISAN_PATH.' utility:testdb');

/*
|--------------------------------------------------------------------------
| Include Standard Autoload File
|--------------------------------------------------------------------------
|
*/

require __DIR__ . '/autoload.php';
```

#### 2. Update `phpunit.xml`

```xml
<phpunit ...
         bootstrap="bootstrap/testing.php"
         ...
>
...
    <php>
        ...
        <!-- Change this to your connection name (if customized) -->
        <env name="DB_CONNECTION" value="testing"/>
    </php>
</phpunit>
```
