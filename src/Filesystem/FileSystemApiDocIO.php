<?php namespace LaravelSwagger\Filesystem;

use LaravelSwagger\OpenApi\ApiDocIO;
use Symfony\Component\Yaml\Yaml;

/**
 * Class FileSystemApiDocIO
 * @package LaravelSwagger\Filesystem
 */
class FileSystemApiDocIO implements ApiDocIO
{
    private string $pathToRead;

    private string $yamlContent;

    private array $aliases = [];

    public function update(string $path, callable $modifier)
    {
        $this->pathToRead = $path;

        $existingApiDoc = $this->parseExisting();

        $modifier($existingApiDoc);
    }

    private function parseExisting(): array
    {
        try {
            $this->readYamlContentFromFile();
            return $this->parseYaml();
        } catch (ReadFailedException $e) {
            return [];
        }
    }

    private function readYamlContentFromFile()
    {
        $pathWithAliasesResolved = $this->applyAliases($this->pathToRead);
        $yamlContent = file_get_contents($pathWithAliasesResolve);
        if ($yamlContent === false) {
            throw new ReadFailedException();
        }
        $this->yamlContent = $yamlContent;
    }

    private function parseYaml(): array
    {
        return Yaml::parse($this->yamlContent);
    }

    public function setAlias(string $name, string $aliasPath)
    {
        $this->aliases[$name] = $aliasPath;
    }

    private function applyAliases($path)
    {
        return str_replace(array_keys($this->aliases), array_values($this->aliases), $path);
    }
}
