<?php namespace LaravelSwaggerTest\Unit;

use LaravelSwagger\OpenApi\ControllerParser;
use LaravelSwagger\PHPDoc\PHPDocControllerParser;
use LaravelSwaggerTest\TestCase;
use LaravelSwaggerTest\Unit\PHPDocController\ControllerWithApiDocPHPDoc;

/**
 * Class PHPDocControllerParserTest
 * @package LaravelSwaggerTest\Unit
 */
class PHPDocControllerParserTest extends TestCase
{
    private PHPDocControllerParser $controller;

    public function setUp() : void
    {
        parent::setUp();
        $this->controller = $this->app->make(PHPDocControllerParser::class);
    }

    /**
     * @test
     */
    public function is_instance_of_controller_parser()
    {
        $this->assertInstanceOf(ControllerParser::class, $this->controller);
    }

    /**
     * @test
     */
    public function parses_phpdoc_property_apidoc_from_classpath()
    {
        $controller = $this->controller->parse(ControllerWithApiDocPHPDoc::class);
        $this->assertEquals(
            '@/app/api-doc.yml',
            $controller->apiDocPath,
            'PHPDocControllerParser did not parse apidoc property from class correctly'
        );
    }
}
