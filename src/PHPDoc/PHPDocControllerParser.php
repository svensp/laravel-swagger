<?php namespace LaravelSwagger\PHPDoc;

use LaravelSwagger\OpenApi\Controller;
use LaravelSwagger\OpenApi\ControllerParser;
use LaravelSwagger\OpenApi\NoApiDocSpecifiedException;
use ReflectionClass;

/**
 * Class PHPDocControllerParser
 * @package LaravelSwagger\PHPDoc
 */
class PHPDocControllerParser implements ControllerParser
{
    private string $classPath;
    private string $comment;
    private string $apiDocPath;
    private array $tags = [];

    public function parse(string $classPath): Controller
    {
        $this->classPath = $classPath;

        $this->parseCommentFromController();
        $this->parseApiDocFromComment();
        $this->parseApiTagsFromComment();

        return $this->buildController();
    }

    /**
     * @param string $classPath
     * @throws \ReflectionException
     */
    private function parseCommentFromController(): void
    {
        $controllerReflection = new ReflectionClass($this->classPath);
        $this->comment = $controllerReflection->getDocComment();
    }

    private function parseApiDocFromComment()
    {
        $apiDocPath = $this->parseTagFromDocs('apidoc');

        $noApiDocPath = empty($apiDocPath);
        if ($noApiDocPath) {
            throw new NoApiDocSpecifiedException("No ApiDoc specified in {$this->classPath}");
        }

        $this->apiDocPath = $apiDocPath;
    }

    private function parseApiTagsFromComment()
    {
        $commaSeparatedTags = $this->parseTagFromDocs('apitags');
        $noTags = empty($commaSeparatedTags);

        if ($noTags) {
            $this->tags = [];
            return;
        }

        $this->tags = $this->explodeAndTrimTags($commaSeparatedTags);
    }

    private function parseTagFromDocs($tag): string
    {
        $matches = [];
        $result = preg_match('~^[^*]*?\*\s*@'.$tag.'\s*([^\s]*)\s*$~m', $this->comment, $matches);

        if ($this->resultIsError($result)) {
            throw new \ErrorException(
                "Result of preg_match while looking for @apidoc phpdoc comment encountered an error"
            );
        }
        if ($this->resultIsNotFound($result)) {
            return '';
        }

        return $matches[1];
    }

    private function explodeAndTrimTags($commaSeparatedTags)
    {
        $tags = explode(',', $commaSeparatedTags);
        return array_map(function ($tag) {
            return trim($tag);
        }, $tags);
    }

    private function buildController()
    {
        $controller = new Controller();
        $controller->path = $this->classPath;
        $controller->apiDocPath = $this->apiDocPath;
        $controller->tags = $this->tags;
        return $controller;
    }

    private function resultIsError($result)
    {
        return $result === false;
    }

    private function resultIsNotFound($result)
    {
        return $result === 0;
    }
}
