# laravel-swagger

this package tries to assist in generating api-doc.yml files for your laravel based apis.

It does not try to generate the whole api-doc.yml for you and thus does not function as complete abstraction layer for
the openapi specification. Instead think of it as `route:list` that makes sure your open-api.yml files have all routes
present and notify you of routes present in the open-api.yml but not in your laravel routes.

- the controller in which a route resides decides the open-api.yml the route appears in via the @apidoc phpdoc entry
- multiple controllers can use the same open-api.yml

## Install

to install the package simply require it with composer:

    composer require svensp/laravel-swagger

## Use

Annotate the controllers which should appear in an open-api.yml with

    /**
    * Class AuthController
    * @apidoc path/to/your/open-api.yml
    */
    class AuthController {
    ...

and have them created with

    ./artin open-api:generate

It is however recommended to publish the config file and use an alias:

    ./artisan vendor:publish --provider=LaravelSwagger\LaravelSwaggerProvider

The default settings map `@` to `app_path()`, personally I map the name `default` to `resource_path('/open-api.yml')`

    /**
    * Class AuthController
    * @apidoc @/resources/open-api.yml
    * OR
    * @apidoc default
    * 
    */
    class AuthController {
    ...
    
### Tags

If you use the same open-api.yml for multiple controllers then you probably want to have the same tags set for all
routes of the same controller. You an use the `@apitags` phpdoc directive to have this done automatically for you:

    /**
    * Class AuthController
    * @apidoc default
    * @apitags auth
    *
    */
    class AuthController {
    ...

    /**
    * Class NewsController
    * @apidoc default
    * @apitags news
    *
    */
    class NewsController {
    ...

the list of tags is parsed as comma separated list.

## Roadmap

- (maybe) add parameters defined in FormRequests used by a controller function

## Use outside of Laravel

While I have not split the package in its laravel and open-api components it is possible to use it outside of Laravel.
Simply instantiate the LaravelSwagger\OpenApi\Updater and pass an array of `DefinedRoute`s to its `update` function.
Creating DefinedRoutes from your framework of choice is up to you, as is passing

- templates usually loaded from the config
- aliases usually loaded from the config
- callbacks - set in the laravel command to echo having foud a controller without @apidoc directive or routes in the
  open-api.yml which are no longer present in your DefinedRoutes