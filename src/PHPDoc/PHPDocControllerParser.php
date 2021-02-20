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

    public function parse(string $classPath): Controller
    {
        $this->classPath = $classPath;

        $this->parseCommentFromController();
        $this->parseApiDocFromComment();

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
        $matches = [];
        $result = preg_match('~^[^*]*?\*\s*@apidoc\s*([^\s]*)\s*$~m', $this->comment, $matches);

        if ($this->resultIsError($result)) {
            throw new \ErrorException(
                "Result of preg_match while looking for @apidoc phpdoc comment encountered an error"
            );
        }
        if ($this->resultIsNotFound($result)) {
            throw new NoApiDocSpecifiedException("No ApiDoc specified in {$this->classPath}");
        }

        $this->apiDocPath = $matches[1];
    }

    private function buildController()
    {
        $controller = new Controller();
        $controller->path = $this->classPath;
        $controller->apiDocPath = $this->apiDocPath;
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
