# sliver

A tiny PHP test framework.

## Quick start

* Rope in `sliver` as a Composer dependency:
```
# composer require --dev maxvu/sliver
```
* Create test classes by extending `\Sliver\TestSuite\TestSuite`. Use public, non-static functions for each test. Use the constructor and destructor for setup and tear-down. Use private methods and members to help set up and manage the tests.
```php
<?php
use \MyApp\SampleClass;
  class SampleClass extends \Sliver\TestSuite\TestSuite {
    /* ... */
    public function __construct () { /* ... setup */ }
    public function __destruct () { /* ... tear down */ }
    private function someUtility () { /* ... perform some repetitive thing */ }
    public function test1 () { /* ... */ }
    public function test2 () { /* ... */ }
    public function test3 () { /* ... */ }
  }
```
* Make your classes PSR-4 available under `autoload-dev` and add sliver as a `script`:
```json
    "autoload-dev" : {
        "psr-4": {
            "MyApp\\Tests" : "tst/"
        }
    },
    "scripts" : {
      "sliver" : ["Sliver\\Sliver::run"]
    }
```
* Use `$this->assert( ... )->{...}` to apply conditions for the test's success and `$this->spy( $className )` to simulate behavior.
* Use the Composer script to run and get output to the console:
```
[user@host myapp]# composer sliver

   [    ] Starting sliver
      [    ] Scanning autoload directories
      [    ] Found 1 test suites in 0.00598s

   [    ] MyApp\Tests\BoxTest
   [ OK ] 2 / 2 tests passed in 0.00143s

   [ OK ] 8 / 8 conditions, 2 / 2 tests and 1 / 1 suites passed
```
  * If a test fails, get detailed output about the result of the test:
 ```
      [user@host myapp]# composer sliver

      [    ] Starting sliver
      [    ] Scanning autoload directories
      [    ] Found 1 test suites in 0.00824s

      [    ] MyApp\Tests\BoxTest
         [    ] shakeDoesntDropThings, 2 / 3 in 0.00047s
               [    ] output:       ""
               [    ] return value: NULL
               [    ] exception:    exception "Box has too many things" (code 0)
               [    ] duration:     0.00047s
               [ OK ] condition:    int 7 === int 7
               [ OK ] condition:    TRUE === TRUE
               [ !! ] condition:    should not throw exception
        [ !! ] 1 / 2 tests passed in 0.00153s

        [ !! ] 7 / 8 conditions, 1 / 2 tests and 0 / 1 suites passed
```

## Asserts

Use sliver's `assert()` vocabulary to define test criteria. `assert( $x )` is available by class extension and produces a handle to a generator that applies conditions to the current test.
```php
<?php
public function someTest () {
	$thing = new MyApp\Box();
    $thing->value = "HELLO";
    $this->assert( $thing->has( "HELLO" ) )->true();
}
```
Here, `true()` produces a condition that requires the result of `$thing->has( "HELLO" )` to equal true. Here are a list of available conditions:

```
	Generically
    	eq( $y ), equals( $y )          $x === $y
        ne( $y ), notEquals( $y )       $x != $y
        null(), isNull()				$x === NULL
        true()                          $x === TRUE
        false()                         $x === FALSE
    Arrays
        eq( $y )                        $x == $y [$x and $y share K/V mapping]
        has( $el ), contains( $el )     in_array( $el, $x )
        hasNot( $el )                   !in_array( $el, $x )
        hasKey( $k )                    array_has_key( $k, $x )
        size( $n ), len( $n ),
          length( $n )                  sizeof( $x ) === $n
    Strings
    	len( $n ), size( $n )           strlen( $x ) === $n
        contains( $substr )             strpos( $x, $substr ) !== FALSE
        matches( $pattern )             preg_match( $pattern, $x ) !== FALSE
    Numeric
    	gt( $y )                        $x > $y
        ge( $y ), gte( $y )             $x >= $y
    	lt( $y )                        $x < $y
        le( $y ), lte( $y )             $x <= $y
```

All the asserts are chainable, are applied to the test they're contained in as a condition and are listed in test output by the order they appear in the test.

## Special conditions

For situations where it would be more convenient to apply conditions to the test, rather than the result of a constructed closure, there are a few special conditions that do not apply to a value and do not use `assert()`:

```
	shouldThrowException ( $msg = NULL, $code = NULL )
	shouldTakeLessThan ( $secs )
    shouldReturn ( $value )
    shouldOutput ( $txt )
```

Note that `shouldThrowException` should be declared before the exception is thrown.

## Spies

Use spies to simulate and verify behavior of arbitrary classes. To instantiate, use the `spy( $className, $ctorArgs = NULL )` method with `$className` being the namespaced class and `$ctorArgs` being an array of arguments to pass to the constructor (pass `NULL` to create without invoking its constructor).

```php
<?php
	public function someOtherTest () {
    	$counter = $this->spy( 'MyApp\Counter' );
    }
```

Treat the spy as any object of that type, but share public access to its private members and functions:

```php
<?php
	public function someOtherTest () {
    	$counter = $this->spy( 'MyApp\Counter', array( 0 ) );
        $counter->inc()->inc();
        echo $counter->getCount(); // 2
        $counter->count = 0;
        echo $counter->getCount(); // 0
    }
```

Use the `summon()` command to get access to the spy's controller, from which you can replace any instance method with the `stub( $fn_name, $closure )` method:

```php
<?php
	public function someOtherTest () {
    	$counter = $this->spy( 'MyApp\Counter', array( 0 ) );
        $counter->summon()->stub( 'inc', function () { $this->count += 10; } );
        $counter->inc();
        echo $counter->getCount(); // 10
    }
```

## Caveats and Known Limitations

#### Detection
  * Test class files must have a `.php` extension.
  * Test class files must contain, somewhere in, the partial namespace `Sliver\TestSuite`.
  * Test class files must be contained in some descendent folder of one mentioned in `autoload-dev`.
  

#### Tests
  * Tests may not use methods named `assert`, `spy`, or any shared in the Special Conditions above.
  

#### Spies
  * Spies will not obey static calls. You may not call static functions on the instance and member functions using `self::` or `static::` scoping will not resolve.
  * Spies (probably) do not recognize class constants.
