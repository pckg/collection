# pckg/collection

[![Codacy Badge](https://api.codacy.com/project/badge/Grade/f98a48ae40b0432195e52c479115fcff)](https://www.codacy.com/app/schtr4jh/collection?utm_source=github.com&utm_medium=referral&utm_content=pckg/collection&utm_campaign=badger)


Pckg/collection provides a ways handle collection of items / arrays and strings differently. It works really well with [pckg/skeleton](https://github.com/pckg/skeleton), [pckg/framework](https://github.com/pckg/framework) and [pckg/database](https://github.com/pckg/database).

# Installation

For standalone usage simply require pckg/collection in composer.

```bash
$ composer require pckg/collection
```

For advanced usage check pckg/skeleton.

```bash
$ composer install pckg/skeleton .
```

## Dependencies

Package does not depend on any other package.

# Tests

Test can be run with codeception

```bash
$ cp ./codeception.sample.yml ./codeception.yml
$ codecept run
```
# Simple usage

```php
// create a new Collection

$collection = new Collection();

// push items to the last position of the collection

// push some single items

$collection->push('foo');
$collection->push('bar');

// push a whole array

$collection->pushArray(['first', 'second']);

// pop last item and remove it from collection

$item = $collection->pop();

// add an item at the first position

$collection->prepend('prepended');

// retrieve and remove first item

$item = $collection->shift();

// retrieve first and last items keeping then in colection

$collection->first();
$collection->last();

```
