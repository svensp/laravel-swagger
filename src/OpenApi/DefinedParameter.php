<?php namespace LaravelSwagger\OpenApi;

/**
 * Class DefinedParameter
 * @package LaravelSwagger\OpenApi
 */
class DefinedParameter
{

    public string $name;

    public string $description = 'TODO Description';

    public static function fromName(string $name): self
    {
        $instance = new self;
        $instance->name = $name;
        return $instance;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }
}
