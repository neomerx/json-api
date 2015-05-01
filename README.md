[![Version](https://img.shields.io/packagist/v/neomerx/json-api.svg)](https://packagist.org/packages/neomerx/json-api)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/neomerx/json-api/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/neomerx/json-api/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/neomerx/json-api/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/neomerx/json-api/?branch=master)
[![Build Status](https://travis-ci.org/neomerx/json-api.svg?branch=master)](https://travis-ci.org/neomerx/json-api)
[![HHVM](https://img.shields.io/hhvm/neomerx/json-api.svg)](https://travis-ci.org/neomerx/json-api)
[![License](https://img.shields.io/packagist/l/neomerx/json-api.svg)](https://packagist.org/packages/neomerx/json-api)

## Description

Framework agnostic [JSON API](http://jsonapi.org/) implementation.

This package covers encoding PHP objects to JavaScript Object Notation (JSON) as described in [JSON API Format](http://jsonapi.org/format/).

* Resource attributes and complex attributes
* Meta information for document, resources and link objects
* Link objects (including links as references, links to null and empty arrays)
* Compound documents with included resources
* Limits for input data parsing depth
* Circular references in resources
* Sparse fieldset filter rules
* Pagination links
* Errors

## Versioning

This package implements the latest [JSON API](http://jsonapi.org/) version RC3 and is using [Semantic Versioning](http://semver.org/).
The package version reflects the fact JSON API specification has not been finally released yet but not the package readiness.

## Install

Via Composer

```
$ composer require neomerx/json-api ~0.1
```

## Usage

### Basic usage

```php
$encoder = Encoder::instance([
    Author::class  => AuthorSchema::class,
], new JsonEncodeOptions(JSON_PRETTY_PRINT));

echo $encoder->encode($author) . PHP_EOL;
```

will output

```json
{
    "data": {
        "type": "people",
        "id": "123",
        "first_name": "John",
        "last_name": "Dow",
        "links": {
            "self": "http:\/\/example.com\/people\/123"
        }
    }
}
```

The ```AuthorSchema``` looks like

```php
class AuthorSchema extends SchemaProvider
{
    protected $resourceType = 'people';
    protected $baseSelfUrl  = 'http://example.com/people/';

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

### Object hierarchy with included objects

```php
$encoder  = Encoder::instance([
    Author::class  => AuthorSchema::class,
    Comment::class => CommentSchema::class,
    Post::class    => PostSchema::class,
    Site::class    => SiteSchema::class
], new JsonEncodeOptions(JSON_PRETTY_PRINT));

echo $encoder->encode($site) . PHP_EOL;
```

will output

```json
{
    "data": {
        "type": "sites",
        "id": "1",
        "name": "JSON API Samples",
        "links": {
            "self": "http:\/\/example.com\/sites\/1",
            "posts": {
                "linkage": {
                    "type": "posts",
                    "id": "321"
                }
            }
        }
    },
    "included": [
        {
            "type": "people",
            "id": "123",
            "first_name": "John",
            "last_name": "Dow"
        },
        {
            "type": "comments",
            "id": "456",
            "body": "Included objects work as easy as basic ones",
            "links": {
                "self": "http:\/\/example.com\/comments\/456",
                "author": {
                    "linkage": {
                        "type": "people",
                        "id": "123"
                    }
                }
            }
        },
        {
            "type": "comments",
            "id": "789",
            "body": "Let's try!",
            "links": {
                "self": "http:\/\/example.com\/comments\/789",
                "author": {
                    "linkage": {
                        "type": "people",
                        "id": "123"
                    }
                }
            }
        },
        {
            "type": "posts",
            "id": "321",
            "title": "Included objects",
            "body": "Yes, it is supported",
            "links": {
                "author": {
                    "linkage": {
                        "type": "people",
                        "id": "123"
                    }
                },
                "comments": {
                    "linkage": [
                        {
                            "type": "comments",
                            "id": "456"
                        },
                        {
                            "type": "comments",
                            "id": "789"
                        }
                    ]
                }
            }
        }
    ]
}
```

### Sparse and fields sets

Output result could be filtered by included relations and object attributes.

```php
$options  = new EncodingOptions(
    ['posts.author'], // Paths to be included
    [
        // Attributes and links that should be shown
        'sites'  => ['name'],
        'people' => ['first_name'],
    ]
);
$encoder  = Encoder::instance([
    Author::class  => AuthorSchema::class,
    Comment::class => CommentSchema::class,
    Post::class    => PostSchema::class,
    Site::class    => SiteSchema::class
], new JsonEncodeOptions(JSON_PRETTY_PRINT));

echo $encoder->encode($site, null, null, $options) . PHP_EOL;
```

Note that output does not contain objects from neither ```posts``` nor ```posts.comments``` relations. Attributes of ```site``` and ```author``` are filtered as well.

```json
{
    "data": {
        "type": "sites",
        "id": "1",
        "name": "JSON API Samples",
        "links": {
            "self": "http:\/\/example.com\/sites\/1",
            "posts": {
                "linkage": {
                    "type": "posts",
                    "id": "321"
                }
            }
        }
    },
    "included": [
        {
            "type": "people",
            "id": "123",
            "first_name": "John"
        }
    ]
}
```

### View customization

You can fully customize how your output result will look like

* Add links to document.
* Add meta to document, resource objects or link objects.
* Show object links as references.
* Show/hide ```self```, ```related```, ```links```, ```meta``` for each resource type individually for both main and included objects and links.
* Specify what links should place resources to ```included``` section and set default inclusion depth for each resource type independently.

### Sample source code

The full sample source code could be found [here](sample/)

## Questions?

Do not hesitate to contact us on [![Gitter](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/neomerx/json-api) or post an [issue](https://github.com/neomerx/json-api/issues).

## Testing

``` bash
$ phpunit
```

## Credits

- [Neomerx](https://github.com/neomerx)
- [All Contributors](../../contributors)

## License

Apache License (Version 2.0). Please see [License File](LICENSE) for more information.
