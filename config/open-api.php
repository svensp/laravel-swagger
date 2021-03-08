<?php

use Symfony\Component\Yaml\Yaml;

return [
    'aliases' => [
        '@' => app_path(),
    ],
    'template' => Yaml::parseFile(resource_path('open-api.tpl.yaml')),
    'route-template' => Yaml::parseFile(resource_path('route.tpl.yaml'))
];
