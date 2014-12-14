## Cart

A simple cart implementation.

## Docs

Coming Soonish maybe.

## Overview
Look at one of the following topics to learn more about LaravelShoppingcart

* [Usage](#usage)
* [Collections](#collections)
* [Instances](#instances)
* [Models](#models)
* [Exceptions](#exceptions)
* [Events](#events)
* [Example](#example)

## Usage

The cart provides the following packages:

### Adding Items

```php

// Basic form
$cart->add([
    'id' => 'LEA_1',
    'name' => 'Product 1',
    'quantity' => 1,
    'price' => 1789.43,
    'options' => [
        'condition' => 'nm',
        'style' => 'normal'
    ]
]);

// Add multiple items
$cart->add([
    [
        'id' => 'LEA_1',
        'name' => 'Product 1',
        'quantity' => 1,
        'price' => 1789.43,
        'options' => [
            'condition' => 'nm',
            'style' => 'normal'
        ]
    ],
    [
        'id' => 'KTK_8',
        'name' => 'Product 2',
        'quantity' => 1,
        'price' => 12.43,
        'options' => [
            'condition' => 'sp',
            'style' => 'foil'
        ]
    ]
]);

```

### Updating Items

```php
$cart->update($cart->content()->first()->rowId, [ 'quantity' => 2 ]);
```

### Removing Items

```php
$cart->remove($cart->content()->first()->rowId);
```

### Retrieve Items

```php
$cart->get($_POST['rowId']);
```

### Get the cart content

```php
$cart->content();
```

### Cart Destruction

```php
$cart->destroy();
```

### Get the total of the cart items

```php
$cart->total();
```

### Get the number of items in the cart

```php
 $cart->count();      // Total items
 $cart->countRows(); // Total rows
```

**Cart::search()**

```php
/**
 * Search if the cart has a item
 *
 * @param  Array  $search An array with the item ID and optional options
 * @return Array|boolean
 */

 Cart::search(array('id' => 1, 'options' => array('size' => 'L'))); // Returns an array of rowid(s) of found item(s) or false on failure
```

## Instances

Now the packages also supports multiple instances of the cart. The way this works is like this:

You can set the current instance of the cart with `Cart::instance('newInstance')`, at that moment, the active instance of the cart is `newInstance`, so when you add, remove or get the content of the cart, you work with the `newInstance` instance of the cart.
If you want to switch instances, you just call `Cart::instance('otherInstance')` again, and you're working with the `otherInstance` again.

So a little example:

```php
Cart::instance('shopping')->add('192ao12', 'Product 1', 1, 9.99);

// Get the content of the 'shopping' cart
Cart::content();

Cart::instance('wishlist')->add('sdjk922', 'Product 2', 1, 19.95, array('size' => 'medium'));

// Get the content of the 'wishlist' cart
Cart::content();

// If you want to get the content of the 'shopping' cart again...
Cart::instance('shopping')->content();

// And the count of the 'wishlist' cart again
Cart::instance('wishlist')->count();
```

N.B. Keep in mind that the cart stays in the last set instance for as long as you don't set a different one during script execution.

N.B.2 The default cart instance is called `main`, so when you're not using instances,`Cart::content();` is the same as `Cart::instance('main')->content()`.

## Exceptions
The Cart package will throw exceptions if something goes wrong. This way it's easier to debug your code using the Cart package or to handle the error based on the type of exceptions. The Cart packages can throw the following exceptions:

| Exception                             | Reason                                                                           |
| ------------------------------------- | --------------------------------------------------------------------------------- |
| *Laraverse\Cart\Instance*             | When no instance is passed to the instance() method                              |
| *Laraverse\Cart\InvalidItem*          | When a new product misses one of it's arguments (`id`, `name`, `qty`, `price`)   |
| *Laraverse\Cart\InvalidPrice*         | When a non-numeric price is passed                                               |
| *Laraverse\Cart\InvalidQty*           | When a non-numeric quantity is passed                                            |
| *Laraverse\Cart\InvalidRowID*         | When the `$rowId` that got passed doesn't exists in the current cart             |
| *Laraverse\Cart\UnknownModel*         | When an unknown model is associated to a cart row                                |

## Events

Events are available for you to program custom logic before or after actions are carried out. To stop the action, throw an exception.

| Event                       | Fired                                   |
| ----------------------      | --------------------------------------- |
| cart.adding($data)          | When an item is about to be added to the cart |
| cart.added($item)           | After an item has been added to the cart      |
| cart.updating($item, $data) | Before an existing item is updated |
| cart.updated($item)         | When an item in the cart is updated     |
| cart.removing($item)        | When an item is about to be removed from the cart |
| cart.removed($item)         | When an item is removed from the cart   |
| cart.destroying($cart)           | When the cart is about to be destoryed |
| cart.destroyed()            | When the cart is destroyed              |


