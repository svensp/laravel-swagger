# laravel-swagger

this package tries to assist in generating api-doc.yml files for your laravel based apis.

It does not try to generate the whole api-doc.yml for you and thus does not function as complete abstraction layer for
the openapi specification. Instead think of it as `route:list` that makes sure your api-doc.yml files have all routes
present and notify you of routes present in the api-doc.yml but not in your laravel routes.

- the controller in which a route resides decides the api-doc.yml the route appears in via the @apidoc phpdoc entry
- multiple controllers can use the same api-doc.yml

## Install

to install the package simply require it with composer:

    composer require svensp/laravel-swagger

## Roadmap

- make it work
- allow setting a template for a newly created api-doc.yml
- allow setting a template for a newly added route
- automatically add route parameters to Operation Objects parameters entry
- (maybe) add parameters defined in FormRequests used by 
