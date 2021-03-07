<?php namespace LaravelSwaggerTest\Unit;

use LaravelSwagger\Controllers\ApiDocController;
use LaravelSwagger\Controllers\Cache;
use LaravelSwagger\Controllers\DoAfterRequestSent;
use LaravelSwagger\Controllers\ResponseBuilder;
use LaravelSwaggerTest\TestCase;
use Mockery;

/**
 * Class ApiDocControllerTest
 * @package LaravelSwaggerTest\Unit
 */
class ApiDocControllerTest extends TestCase
{

    /**
     * @var ApiDocController
     */
    private $apiDocController;

    /**
     * @var Cache|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    private $cache;

    /**
     * @var DoAfterRequestSent|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    private $doAfterRequestSent;

    /**
     * @var ResponseBuilder|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    private $responseBuilder;

    public function setUp() : void
    {
        parent::setUp();

        $this->cache = $this->mockAndRegisterInstance(Cache::class);
        $this->doAfterRequestSent = $this->mockAndRegisterInstance(DoAfterRequestSent::class);
        $this->responseBuilder = $this->mockAndRegisterInstance(ResponseBuilder::class);

        $this->apiDocController = app(ApiDocController::class);
        $this->apiDocController->setFilepath('');
    }

    /**
     * @test
     */
    public function returns_response_from_response_builder()
    {
        $expectedResponse = $this->faker->text;
        $this->responseBuilder->shouldReceive('jsonResponse')->andReturn($expectedResponse);

        $response = $this->apiDocController->sendApiDoc();

        $this->assertEquals($expectedResponse, $response);
    }

    /**
     * @test
     */
    public function remembers_and_builds_response_from_json_from_cache()
    {
        $this->apiDocController->setFilepath('test.yml');

        $expectedResult = $this->faker->text;
        $this->cache->shouldReceive('remember')
            ->with('test.yml.json', callableValue(), intValue())
            ->andReturn($expectedResult);
        $this->apiDocController->sendApiDoc();

        $this->responseBuilder->shouldHaveReceived('jsonResponse')->with($expectedResult);
    }

    /**
     * @test
     */
    public function cache_remember_callback_builds_json_data_from_yml()
    {
        $this->apiDocController->setFilepath(__DIR__.'/data/api-doc.yml');

        $result = [];
        $this->cache->shouldReceive('remember')
            ->withArgs(function ($key, $do) use (&$result) {
                $this->assertEquals(__DIR__.'/data/api-doc.yml.json', $key);
                $result = $do();
                return true;
            });
        $this->apiDocController->sendApiDoc();

        $this->assertArrayHasKey('testkey', $result);
        $this->assertEquals('testvalue', $result['testkey']);
    }

    /**
     * @test
     */
    public function can_set_cache_ttl()
    {
        $this->apiDocController->setCacheTtlInMilliseconds(1000);

        $this->apiDocController->sendApiDoc();

        $this->cache->shouldHaveReceived('remember')->with(stringValue(), callableValue(), 1000);
    }
}
