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

## Callbacks

### has

Testing if collection has a set of values:

```php
$collection = new Collection([
                                 'foo' => [
                                     'id'    => 1,
                                     'title' => 'baz',
                                 ],
                                 'bar' => [
                                     'id'    => 2,
                                     'title' => 'unknown',
                                 ],
                             ]);

$collection->has(['id' => 1, 'title' => 'baz'])); // return true

$collection->has(['id' => 2, 'title' => 'baz'])); // return false
```

### Filtering entries

Return all itens that has a `true` return from annonymous function.

```php
$filtered = $collection->filter(function($item) {
    return $item['id'] == 1;
});

var_dump($filtered->all()); // ['foo' => ['id' => 1, 'title' => 'baz']]
```

### Seting keys

Set entry keys by some array item value. At following sample all keys are setted by `title` inner array entry.

```php
$keyed = $collection->keyBy('title');

var_dump($keyed->all()); 

/**
* [
*     'baz'     => [
*         'id'    => 1,
*         'title' => 'baz',
*     ],
*     'unknown' => [
*         'id'    => 2,
*         'title' => 'unknown',
*     ],
* ]
**/
```

### Get first item by satisfied check

Return the firt item that satisfies a logical test.

```php
$first = $collection->first(function($item) { 
   return $item['id'] > 1; 
});

var_dump($first); //['id' => 2, 'title' => 'unknown']
```

### Map key by inner value

Map the key of each entry to a inner value based on inner key.

```php
$mapped = $collection->map('title');

var_dump($mapped->all()); // ['foo' => 'baz', 'bar' => 'unknown']
```
