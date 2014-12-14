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

The shoppingcart gives you the following methods to use:

**Cart::add()**

```php
/**
 * Add a row to the cart
 *
 * @param string|Array $id      Unique ID of the item|Item formated as array|Array of items
 * @param string       $name    Name of the item
 * @param int          $qty     Item qty to add to the cart
 * @param float        $price   Price of one item
 * @param Array        $options Array of additional options, such as 'size' or 'color'
 */

// Basic form
Cart::add('293ad', 'Product 1', 1, 9.99, array('size' => 'large'));

// Array form
Cart::add(array('id' => '293ad', 'name' => 'Product 1', 'qty' => 1, 'price' => 9.99, 'options' => array('size' => 'large')));

// Batch method
Cart::add(array(
  array('id' => '293ad', 'name' => 'Product 1', 'qty' => 1, 'price' => 10.00),
  array('id' => '4832k', 'name' => 'Product 2', 'qty' => 1, 'price' => 10.00, 'options' => array('size' => 'large'))
));
```

**Cart::update()**

```php
/**
 * Update the quantity of one row of the cart
 *
 * @param  string        $rowId       The rowid of the item you want to update
 * @param  integer|Array $attribute   New quantity of the item|Array of attributes to update
 * @return boolean
 */
 $rowId = 'da39a3ee5e6b4b0d3255bfef95601890afd80709';

Cart::update($rowId, 2);

OR

Cart::update($rowId, array('name' => 'Product 1'));
```

**Cart::remove()**

```php
/**
 * Remove a row from the cart
 *
 * @param  string  $rowId The rowid of the item
 * @return boolean
 */

 $rowId = 'da39a3ee5e6b4b0d3255bfef95601890afd80709';

Cart::remove($rowId);
```

**Cart::get()**

```php
/**
 * Get a row of the cart by its ID
 *
 * @param  string $rowId The ID of the row to fetch
 * @return CartRowCollection
 */

$rowId = 'da39a3ee5e6b4b0d3255bfef95601890afd80709';

Cart::get($rowId);
```

**Cart::content()**

```php
/**
 * Get the cart content
 *
 * @return CartCollection
 */

Cart::content();
```

**Cart::destroy()**

```php
/**
 * Empty the cart
 *
 * @return boolean
 */

Cart::destroy();
```

**Cart::total()**

```php
/**
 * Get the price total
 *
 * @return float
 */

Cart::total();
```

**Cart::count()**

```php
/**
 * Get the number of items in the cart
 *
 * @param  boolean $totalItems Get all the items (when false, will return the number of rows)
 * @return int
 */

 Cart::count();      // Total items
 Cart::count(false); // Total rows
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

## Collections

As you might have seen, the `Cart::content()` and `Cart::get()` methods both return a Collection, a `CartCollection` and a `CartRowCollection`.

These Collections extends the 'native' Laravel 4 Collection class, so all methods you know from this class can also be used on your shopping cart. With some addition to easily work with your carts content.

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
| cart.destroying()           | When the cart is about to be destoryed |
| cart.destroyed()            | When the cart is destroyed              |


