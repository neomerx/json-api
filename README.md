[![Build Status](https://travis-ci.org/neomerx/json-api.svg?branch=master)](https://travis-ci.org/neomerx/json-api)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/neomerx/json-api/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/neomerx/json-api/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/neomerx/json-api/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/neomerx/json-api/?branch=master)
[![License](https://img.shields.io/packagist/l/neomerx/json-api.svg)](https://packagist.org/packages/neomerx/json-api)

## Description 

<a href="http://jsonapi.org/" target="_blank"><img src="http://jsonapi.org/images/jsonapi.png" alt="JSON API logo" title="JSON API" align="right" width="415" height="130" /></a>

A good API is one of most effective ways to improve the experience for your clients. Standardized approaches for data formats and communication protocols increase productivity and make integration between applications smooth.

This framework agnostic package implements [JSON API](http://jsonapi.org/) specification **version v1.1** and helps focusing on core application functionality rather than on protocol implementation. It supports document structure, errors, data fetching as described in [JSON API Format](http://jsonapi.org/format/) and covers parsing and checking HTTP request parameters and headers. For instance it helps to correctly respond with ```Unsupported Media Type``` (HTTP code 415) and ```Not Acceptable``` (HTTP code 406) to invalid requests. You don't need to manually validate all input parameters on every request. You can configure what parameters are supported by your services and this package will check incoming requests automatically. It greatly simplifies API development and fully support specification. In particular

* Resource attributes and relationships
* Polymorphic resource data and relationships
* Compound documents with inclusion of related resources (circular resource references supported)
* Meta information for document, resources, errors, relationship and link objects
* Profiles 
* Parsing HTTP `Accept` and `Content-Type` headers in accordance with [RFC 7231](https://tools.ietf.org/html/rfc7231)
* Parsing HTTP query parameters (e.g. pagination, sorting and etc)
* Sparse fieldsets and customized included paths
* Errors

High code quality and **100% test coverage** with **150+ tests**. Production ready.

**To find out more, please check out the [Wiki](https://github.com/neomerx/json-api/wiki) and [Sample App](/sample)**.

<blockquote align="right">
    &ldquo;I'm loving how easy it makes it to quickly implement an api&rdquo;
    <p>&ndash; <em>Jeremy Cloutier</em></p>
</blockquote>

## Full-stack Integration

This package is framework agnostic and if you are looking for practical usage sample you might be interested in Quick start JSON API server application [Limoncello App](https://github.com/limoncello-php/app).

The server supports
- CRUD operations for a few sample data models and Users.
- Cross-origin requests (CORS) to API server.
- Authentication (Bearer token) and authorizations for CRUD operations.
- Support for such JSON API features as resource inclusion, pagination and etc.

<p align="center">
<a href="https://github.com/limoncello-php/app" target="_blank"><img src="https://raw.githubusercontent.com/limoncello-php/app/master/server/resources/img/screen-shot.png" alt="Demo app screen-shot" title="Limoncello App" align="middle" width="330" height="252" /></a>
</p>

## Sample usage

Assuming you've got an ```$author``` of type ```\Author``` you can encode it to JSON API as simple as this

```php
$encoder = Encoder::instance([
        Author::class => AuthorSchema::class,
    ])
    ->withUrlPrefix('http://example.com/api/v1')
    ->withEncodeOptions(JSON_PRETTY_PRINT);

echo $encoder->encodeData($author) . PHP_EOL;
```

will output

```json
{
    "data" : {
        "type"       : "people",
        "id"         : "123",
        "attributes" : {
            "first-name": "John",
            "last-name": "Doe"
        },
        "relationships" : {
            "comments" : {
                "links": {
                    "related" : "http://example.com/api/v1/people/123/comments"
                }
            }
        },
        "links" : {
            "self" : "http://example.com/api/v1/people/123"
        }
    }
}
```

The ```AuthorSchema``` provides information about resource's attributes and might look like

```php
class AuthorSchema extends BaseSchema
{
    public function getType(): string
    {
        return 'people';
    }

    public function getId($author): ?string
    {
        return $author->authorId;
    }

    public function getAttributes($author): iterable
    {
        return [
            'first-name' => $author->firstName,
            'last-name'  => $author->lastName,
        ];
    }

    public function getRelationships($author): iterable
    {
        return [
            'comments' => [
                self::RELATIONSHIP_LINKS_SELF    => false,
                self::RELATIONSHIP_LINKS_RELATED => true,

                // Data include supported as well as other cool features
                // self::RELATIONSHIP_DATA => $author->comments,
            ],
        ];
    }
}
```

Parameter ```http://example.com/api/v1``` is a URL prefix that will be applied to all encoded links unless they have a flag set telling not to add any prefixes.

Parameter ```JSON_PRETTY_PRINT``` is a PHP predefined [JSON constant](http://php.net/manual/en/json.constants.php).

A sample program with encoding of multiple, nested, filtered objects and more is [here](sample).

**For more advanced usage please check out the [Wiki](https://github.com/neomerx/json-api/wiki)**.

## Versions

Current version is 3.x (PHP 7.1+) for older PHP (PHP 5.5 - 7.0, HHVM) please use version 1.x.

## Questions?

Do not hesitate to check [issues](https://github.com/neomerx/json-api/issues) or post a new one.

## Need help?

Are you planning to add JSON API and need help? We'd love to talk to you [sales@neomerx.com](mailto:sales@neomerx.com).

## Contributing

If you have spotted any specification changes that are not reflected in this package please post an [issue](https://github.com/neomerx/json-api/issues). Pull requests for documentation and code improvements are welcome.

There are 2 ways to send pull requests
- small pull requests should be sent to `develop` branch as **1 commit**
- for bigger pull requests (e.g. new features) it's recommended to create an `issue` requesting a new branch for that feature. When a new branch named `feature/issueXX` is created (where `XX` is the issue number) you should post pull requests to this branch. When the feature is completed the branch will be squashed and merged to `develop` and then to `master` branches.

## License

Apache License (Version 2.0). Please see [License File](LICENSE) for more information.
