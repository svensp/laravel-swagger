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

        $modifiedApiDoc = $modifier($existingApiDoc);

        $this->writeModified($modifiedApiDoc);
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

    private function writeModified($modifiedData)
    {
        $newYaml = $this->convertToYaml($modifiedData);
        $this->writeToFile($newYaml);
    }

    private function readYamlContentFromFile()
    {
        $pathWithAliasesResolved = $this->applyAliases($this->pathToRead);
        if (!file_exists($pathWithAliasesResolved)) {
            throw new ReadFailedException();
        }

        $yamlContent = file_get_contents($pathWithAliasesResolved);
        if ($yamlContent === false) {
            throw new ReadFailedException();
        }
        $this->yamlContent = $yamlContent;
    }

    private function parseYaml(): array
    {
        $content =  Yaml::parse($this->yamlContent);

        if (!is_array($content)) {
            throw new ReadFailedException();
        }

        return $content;
    }

    private function convertToYaml($data)
    {
        return Yaml::dump($data, 8, 2);
    }

    private function writeToFile($newYaml)
    {
        $pathWithAliasesResolved = $this->applyAliases($this->pathToRead);
        file_put_contents($pathWithAliasesResolved, $newYaml);
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
