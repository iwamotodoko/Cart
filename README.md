## Cart

A simple cart implementation.

[![Build Status](https://travis-ci.org/Garbee/Cart.svg?branch=master)](https://travis-ci.org/Garbee/Cart)

## Overview
Look at one of the following topics to learn more about LaravelShoppingcart

* [Usage](#usage)
* [Exceptions](#exceptions)
* [Events](#events)

## Usage

The cart provides the following abilities:

* Create cart instance (default is "main")
* Add item to cart.
* Update item in cart by rowId.
* Remove item in cart by rowId.
* Destroy entire cart instance.
* Get the total of the cart instance.
* Search the cart by any property.
* Get the number of rows in the cart.
* Get the total number of items in the cart.

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

### Search the cart item data

```php
 $cart->search(['name' => 'Black Lotus']); // Returns an array of rowid(s) of found item(s) or false on failure
```

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
| cart.destroying($cart)      | When the cart is about to be destoryed |
| cart.destroyed()            | When the cart is destroyed              |


