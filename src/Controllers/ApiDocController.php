<?php namespace LaravelSwagger\Controllers;

use Symfony\Component\Yaml\Yaml;

/**
 * Class ApiDocController
 * @package LaravelSwagger\Controllers
 */
class ApiDocController
{

    private string $filepath;

    private int $cacheTtlInMs = 500;

    /**
     * @var Cache
     */
    private Cache $cache;

    /**
     * @var DoAfterRequestSent
     */
    private DoAfterRequestSent $doAfterRequestSent;
    /**
     * @var ResponseBuilder
     */
    private ResponseBuilder $responseBuilder;

    /**
     * ApiDocController constructor.
     * @param Cache $cache
     * @param DoAfterRequestSent $doAfterRequestSent
     */
    public function __construct(Cache $cache, DoAfterRequestSent $doAfterRequestSent, ResponseBuilder $responseBuilder)
    {
        $this->cache = $cache;
        $this->doAfterRequestSent = $doAfterRequestSent;
        $this->responseBuilder = $responseBuilder;
    }

    public function sendApiDoc()
    {
        $key = $this->filepath.'.json';
        $json = $this->cache->remember($key, function () {
            $yamlContent = file_get_contents($this->filepath);
            $data = Yaml::parse($yamlContent);
            return json_encode($data);
        }, $this->cacheTtlInMs);

        return $this->responseBuilder->jsonResponse($json);
    }

    public function setFilepath(string $filepath): ApiDocController
    {
        $this->filepath = $filepath;
        return $this;
    }

    /**
     * @param int $cacheTtlInMs
     * @return ApiDocController
     */
    public function setCacheTtlInMilliseconds(int $cacheTtlInMs): ApiDocController
    {
        $this->cacheTtlInMs = $cacheTtlInMs;
        return $this;
    }
}
