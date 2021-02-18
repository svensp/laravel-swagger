<?php namespace LaravelSwaggerTest\Unit;

use LaravelSwagger\Filesystem\FileSystemApiDocIO;
use LaravelSwagger\OpenApi\ApiDocIO;
use LaravelSwaggerTest\TestCase;
use Symfony\Component\Yaml\Yaml;

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
    public function update_via_full_path_receives_correct_previous_data()
    {
        $this->prepareFile(__DIR__.'/data/test.yml');
        $this->assertExistingDataOnUpdate(__DIR__.'/data/test.yml', function ($existingApiDoc) {
            $this->assertExistingApiDocFound($existingApiDoc);
        });
    }

    /**
     * @test
     */
    public function update_via_alias_receives_correct_previous_data()
    {
        $this->prepareFile(__DIR__.'/data/test.yml');

        $this->apiDocIO->setAlias('@', __DIR__.'/data');

        $this->assertExistingDataOnUpdate('@/test.yml', function ($existingApiDoc) {
            $this->assertExistingApiDocFound($existingApiDoc);
        });
    }

    /**
     * @test
     */
    public function update_via_alias_updates_correct_file()
    {
        $this->prepareFile(__DIR__.'/data/test.yml');

        $this->apiDocIO->setAlias('@', __DIR__.'/data');

        $this->apiDocIO->update('@/test.yml', function($previousData) {
            $previousData['was-run'] = 'run';
            return $previousData;
        });

        $this->assertFileUpdated(__DIR__.'/data/test.yml', function($data) {
            $this->assertArrayHas('was-run', $data);
            $this->assertArrayEquals('run', 'was-run', $data);
        });
    }

    /**
     * @test
     */
    public function update_via_full_path_updates_correct_file()
    {
        $this->prepareFile(__DIR__.'/data/test.yml');

        $this->apiDocIO->update(__DIR__.'/data/test.yml', function($previousData) {
            $previousData['was-run'] = 'run';
            return $previousData;
        });

        $this->assertFileUpdated(__DIR__.'/data/test.yml', function($data) {
            $this->assertArrayHas('was-run', $data);
            $this->assertArrayEquals('run', 'was-run', $data);
        });
    }

    private function assertExistingDataOnUpdate($path, callable $asserts)
    {
        $called = false;
        $this->apiDocIO->update($path, function ($previousData) use (&$called, $asserts) {
            $asserts($previousData);
            $called = true;
        });
        $this->assertTrue($called);
    }

    private function assertFileUpdated($path, callable $asserts)
    {
        $resultData = Yaml::parse(file_get_contents($path));
        $asserts($resultData);
    }

    private function assertExistingApiDocFound($existingApiDoc)
    {
        $this->assertArrayEquals('cookies', 'test.data', $existingApiDoc);
        $this->assertArrayEquals('entry1', 'test.array.0', $existingApiDoc);
        $this->assertArrayEquals('entry2', 'test.array.1', $existingApiDoc);
    }

    private function prepareFile(string $path)
    {
        copy(__DIR__.'/data/template.yml', $path);
    }
}
