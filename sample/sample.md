Neomerx JSON API sample application (basic usage)
---
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
            "self": "http:\/\/example.com\/api\/v1\/people\/123"
        }
    }
}
```
Neomerx JSON API sample application (included objects)
---
```json
{
    "data": {
        "type": "sites",
        "id": "1",
        "attributes": {
            "name": "JSON API Samples"
        },
        "relationships": {
            "posts": {
                "data": {
                    "type": "posts",
                    "id": "321"
                }
            }
        },
        "links": {
            "self": "http:\/\/example.com\/sites\/1"
        }
    },
    "included": [
        {
            "type": "people",
            "id": "123",
            "attributes": {
                "first_name": "John",
                "last_name": "Dow"
            }
        },
        {
            "type": "comments",
            "id": "456",
            "attributes": {
                "body": "Included objects work as easy as basic ones"
            },
            "relationships": {
                "author": {
                    "data": {
                        "type": "people",
                        "id": "123"
                    }
                }
            },
            "links": {
                "self": "http:\/\/example.com\/comments\/456"
            }
        },
        {
            "type": "comments",
            "id": "789",
            "attributes": {
                "body": "Let's try!"
            },
            "relationships": {
                "author": {
                    "data": {
                        "type": "people",
                        "id": "123"
                    }
                }
            },
            "links": {
                "self": "http:\/\/example.com\/comments\/789"
            }
        },
        {
            "type": "posts",
            "id": "321",
            "attributes": {
                "title": "Included objects",
                "body": "Yes, it is supported"
            },
            "relationships": {
                "author": {
                    "data": {
                        "type": "people",
                        "id": "123"
                    }
                },
                "comments": {
                    "data": [
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
Neomerx JSON API sample application (sparse and field sets)
---
```json
{
    "data": {
        "type": "sites",
        "id": "1",
        "attributes": {
            "name": "JSON API Samples"
        },
        "links": {
            "self": "\/sites\/1"
        }
    },
    "included": [
        {
            "type": "people",
            "id": "123",
            "attributes": {
                "first_name": "John"
            }
        },
        {
            "type": "posts",
            "id": "321"
        }
    ]
}
```
Neomerx JSON API sample application (top level links and meta information)
---
```json
{
    "meta": {
        "copyright": "Copyright 2015 Example Corp.",
        "authors": [
            "Yehuda Katz",
            "Steve Klabnik",
            "Dan Gebhardt"
        ]
    },
    "links": {
        "first": "http:\/\/example.com\/people?first",
        "last": "http:\/\/example.com\/people?last",
        "prev": "http:\/\/example.com\/people?prev",
        "next": "http:\/\/example.com\/people?next"
    },
    "data": {
        "type": "people",
        "id": "123",
        "attributes": {
            "first_name": "John",
            "last_name": "Dow"
        },
        "links": {
            "self": "http:\/\/example.com\/people\/123"
        }
    }
}
```