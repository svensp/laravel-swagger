<?php namespace LaravelSwaggerTest;

use Faker\Factory;
use Illuminate\Support\Arr;
use Mockery;

/**
 * Class TestCase
 * @package LaravelSwaggerTest\Unit
 */
abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    protected \Faker\Generator $faker;

    public function setUp() : void {
    	parent::setUp();
    	$this->faker = Factory::create();
    	\Hamcrest\Util::registerGlobalFunctions();
    }

    protected function mockAndRegisterInstance(string $classPath)
    {
        $instance = Mockery::spy($classPath);
        $this->app->instance($classPath, $instance);
        return $instance;
    }

    protected function isCallable()
    {
        return $this->callback(function($parameter) {
            return is_callable($parameter);
        });
    }

    protected function assertArrayHas($expectedKey, $array)
    {
        $this->assertTrue( Arr::has($array, $expectedKey), "Array does not have expected key $expectedKey" );
    }

    protected function assertArrayEquals($expectedValue, $expectedKey, $array)
    {
        $this->assertEquals($expectedValue, Arr::get($array, $expectedKey), "Array does not have expected key $expectedKey" );
    }

}