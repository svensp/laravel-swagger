<?php namespace LaravelSwaggerTest\Unit;

use LaravelSwagger\Filesystem\FileSystemApiDocIO;
use LaravelSwagger\OpenApi\ApiDocIO;
use LaravelSwaggerTest\TestCase;

/**
 * Class FilesystemApiDocIO
 * @package LaravelSwaggerTest\Unit
 */
class FilesystemApiDocIOTest extends TestCase
{
    /**
     * @var FileSystemApiDocIO
     */
    private FileSystemApiDocIO $apiDocIO;

    public function setUp() : void
    {
        parent::setUp();
        $this->apiDocIO = new FilesystemApiDocIO();
    }

    /**
     * @test
     */
    public function implements_ApiDocIO()
    {
        $this->assertInstanceOf(ApiDocIO::class, $this->apiDocIO);
    }

    /**
     * @test
     */
    public function can_update_via_full_path()
    {
        $this->updateAndAssert(__DIR__.'/data/test.yml', function ($existingApiDoc) {
            $this->assertExistingApiDocFound($existingApiDoc);
        });
    }

    /**
     * @test
     */
    public function can_update_via_alias_in_path()
    {
        $this->apiDocIO->setAlias('@', __DIR__.'/data');

        $this->updateAndAssert('@/test.yml', function ($existingApiDoc) {
            $this->assertExistingApiDocFound($existingApiDoc);
        });
    }

    private function updateAndAssert($path, callable $asserts)
    {
        $called = false;
        $this->apiDocIO->update($path, function ($previousData) use (&$called, $asserts) {
            $asserts($previousData);
            $called = true;
        });
        $this->assertTrue($called);
    }

    private function assertExistingApiDocFound($existingApiDoc)
    {
        $this->assertArrayEquals('cookies', 'test.data', $existingApiDoc);
        $this->assertArrayEquals('entry1', 'test.array.0', $existingApiDoc);
        $this->assertArrayEquals('entry2', 'test.array.1', $existingApiDoc);
    }
}
