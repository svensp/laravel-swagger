<?php namespace LaravelSwaggerTest\Unit;

use LaravelSwagger\OpenApi\ControllerParser;
use LaravelSwagger\OpenApi\NoApiDocSpecifiedException;
use LaravelSwagger\PHPDoc\PHPDocControllerParser;
use LaravelSwaggerTest\TestCase;
use LaravelSwaggerTest\Unit\PHPDocController\ControllerWithApiDocPHPDoc;
use LaravelSwaggerTest\Unit\PHPDocController\ControllerWithoutApiDocPHPDoc;

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

    /**
     * @test
     */
    public function throws_not_found_exception_if_class_has_no_apidoc_property()
    {
        $this->expectException(NoApiDocSpecifiedException::class);
        $this->controller->parse(ControllerWithoutApiDocPHPDoc::class);
    }
}
