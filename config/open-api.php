<?php

use Symfony\Component\Yaml\Yaml;

return [
    /**
     * Aliases are replaced in the @apidoc annotation path
     */
    'aliases' => [
        '@' => app_path(),
    ],

    /**
     * This template is applied when writing the toplevel open-api.yml
     */
    'template' => Yaml::parseFile(resource_path('open-api.tpl.yaml')),

    /**
     * This template is applied when writing each paths.{path}.{method} entru
     */
    'route-template' => Yaml::parseFile(resource_path('route.tpl.yaml'))
];
