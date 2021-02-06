<?php namespace LaravelSwaggerTest;

use Faker\Factory;
use LaravelSwagger\OpenApi\ApiWriter;
use Mockery;

/**
 * Class TestCase
 * @package LaravelSwaggerTest\Unit
 */
class TestCase extends \Orchestra\Testbench\TestCase
{
    protected \Faker\Generator $faker;

    public function setUp() : void {
    	parent::setUp();
    	$this->faker = Factory::create();
    }

    protected function mock(string $property, string $classPath)
    {
        $instance = Mockery::spy($classPath);
        $this->$property = $instance;
        $this->app->instance($classPath, $instance);
    }

}