# Laravel Utilities

This package is intended to be a collection of helpful utilities to assist with the development and maintenance of Laravel Applications.

To install this package, run 
```bash
$ composer require srlabs/laravel-utilities
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

This utility makes it easy to implement the testing stratgey described by [Chris Duell](https://github.com/duellsy) in his blog post *[Speeding up PHP unit tests 15 times](http://www.chrisduell.com/blog/development/speeding-up-unit-tests-in-php/)*.  The essential idea is that, when running tests that require the use of a database persistence layer, running migrations and seeding the database for each test can take a very long time.  He instead suggests creating a sqlite database file ahead of time, and making a new copy of that file for each test instead.  

This package adds an artisan command ('utility:testdb') that will run your migrations and seeds and save them to a pre-defined sqlite file.  Once that is complete, there is a companion trait that you add to your tests which will copy the staging db file to the primary testing db whenever you need it to when running your tests. 

For this to work you need to add at least two sqlite database connections to your ```app/config/testing/database.php``` file. You can name these connections whatever you would like, but by default the package expects them to be called 'setup' and 'testing'. 

```bash
$ php artisan utility:testdb
```

This will run your migrations against the database specified in your 'setup' connection details. 

Next, add the ```TestingDatabaseTrait``` to your test class, and use it as such: 

```php
class FooTest extends TestCase {
    
    use TestingDatabaseTrait;
    
    public function setUp()
    {
      parent::setUp();
     
      $this->prepareDatabase('setup', 'testing');
    }
    
    public function testSomethingIsTrue()
    {
        $this->assertTrue(true);
    }

}
```