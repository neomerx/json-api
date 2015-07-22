[![Project Management](https://img.shields.io/badge/project-management-blue.svg)](https://waffle.io/neomerx/json-api)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/neomerx/json-api/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/neomerx/json-api/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/neomerx/json-api/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/neomerx/json-api/?branch=master)
[![Build Status](https://travis-ci.org/neomerx/json-api.svg?branch=master)](https://travis-ci.org/neomerx/json-api)
[![HHVM](https://img.shields.io/hhvm/neomerx/json-api.svg)](https://travis-ci.org/neomerx/json-api)
[![License](https://img.shields.io/packagist/l/neomerx/json-api.svg)](https://packagist.org/packages/neomerx/json-api)

## Description

<a href="http://jsonapi.org/" target="_blank"><img src="http://jsonapi.org/images/jsonapi.png" alt="JSON API logo" title="JSON API" align="right" width="415" height="130" /></a>

A good API is one of most effective ways to improve the experience for your clients. Standardized approaches for data formats and communication protocols increase productivity and make integration between applications smooth.

This framework agnostic package implements [JSON API](http://jsonapi.org/) specification version **v1.0** and helps focusing on core application functionality rather than on protocol implementation. It supports document structure, errors, data fetching as described in [JSON API Format](http://jsonapi.org/format/) and covers parsing and checking HTTP request parameters and headers. For instance it helps to correctly respond with ```Unsupported Media Type``` (HTTP code 415) and ```Not Acceptable``` (HTTP code 406) to invalid requests. You don't need to manually validate all input parameters on every request. You can configure what parameters are supported by your services and this package will check incoming requests automatically. It greatly simplifies API development and fully support specification. In particular

* Resource attributes and relationships
* Compound documents with inclusion of related resources (circular resource references supported)
* Meta information for document, resources, errors, relationship and link objects
* Parsing HTTP ```Accept``` and ```Content-Type``` headers in accordance with RFC 2616
* Parsing parameters for pagination, sorting and filtering
* Sparse fieldsets and customized included paths
* Errors

High code quality and **100% test coverage** with **150+ tests**. Production ready.

**To find out more, please check out the [Wiki](https://github.com/neomerx/json-api/wiki) and [Sample App](/sample)**.

<blockquote align="right">
    &ldquo;I'm loving how easy it makes it to quickly implement an api&rdquo;
</blockquote>
<p align="right">&ndash;<strong>Jeremy Cloutier</strong></p>

## Sample usage

Assuming you've got an ```$author``` of type ```\Author``` you can encode it to JSON API as simple as this

```php
$encoder = Encoder::instance([
    '\Author' => '\AuthorSchema',
], new EncoderOptions(JSON_PRETTY_PRINT, 'http://example.com/api/v1'));

echo $encoder->encode($author) . PHP_EOL;
```

will output

```json
{
    "data": {
        "type": "people",
        "id": "123",
        "attributes": {
            "first_name": "John",
            "last_name": "Dow"
        },
        "links": {
            "self": "http://example.com/api/v1/people/123"
        }
    }
}
```

The ```AuthorSchema``` provides information about resource's attributes and might look like

```php
class AuthorSchema extends SchemaProvider
{
    protected $resourceType = 'people';
    protected $selfSubUrl   = '/people/';

    public function getId($author)
    {
        /** @var Author $author */
        return $author->authorId;
    }

    public function getAttributes($author)
    {
        /** @var Author $author */
        return [
            'first_name' => $author->firstName,
            'last_name'  => $author->lastName,
        ];
    }
}
```

The first ```EncoderOptions``` parameter ```JSON_PRETTY_PRINT``` is a PHP predefined [JSON constant](http://php.net/manual/en/json.constants.php).

The second ```EncoderOptions``` parameter ```http://example.com/api/v1``` is a URL prefix that will be applied to all encoded links unless they have ```$treatAsHref``` flag set to ```true```.

**For more advanced usage please check out the [Wiki](https://github.com/neomerx/json-api/wiki)**.

## Questions?

Do not hesitate to contact us on [![Gitter](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/neomerx/json-api) or post an [issue](https://github.com/neomerx/json-api/issues).

## Need help?

Are you planning to add JSON API and need help? We'd love to talk to you [sales@neomerx.com](mailto:sales@neomerx.com).

## Contributing

If you have spotted any specification changes that are not reflected in this package please post an [issue](https://github.com/neomerx/json-api/issues). Pull requests for documentation and code improvements are welcome.

Current tasks are managed with [Waffle.io](https://waffle.io/neomerx/json-api).

## License

Apache License (Version 2.0). Please see [License File](LICENSE) for more information.
