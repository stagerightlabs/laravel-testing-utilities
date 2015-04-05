# Laravel Utilities

This package is intended to be a collection of helpful utilities to assist with the development and maintenance of Laravel Applications.

| Laravel Version  | Sentinel Version  | Packagist Branch |
|---|---|---|
| 4.2.*  | 1.*  | ```"srlabs/laravel-testing-utilities": "~1"``` |
| 5.0.*  | 2.*  | ```"srlabs/laravel-testing-utilities": "~2"```   |

To install this package, run 
```bash
$ composer require srlabs/laravel-testing-utilities
```

and then add the service provider to your service providers listing in ```app/config/app.php```

```php 
'providers' => array(
        // ...
	    'SRLabs\Utilities\UtilitiesServiceProvider'
        // ...
	),
```	

## Testing Assistant

*Currently in beta testing.* 

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

This will run your migrations against the database specified in your 'setup' connection details. 

Next, add the ```TestingDatabase``` trait to your test class, and use it as such: 

```php
class FooTest extends TestCase {
    
    use TestingDatabase;
    
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