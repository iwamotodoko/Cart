<?php namespace Laraverse\Cart;

use Illuminate\Contracts\Events\Dispatcher;
use Laraverse\Cart\Collections\Cart as CartCollection;
use Laraverse\Cart\Collections\Row as CartRowCollection;
use Laraverse\Cart\Collections\RowOptions as CartRowOptionsCollection;

class Cart
{

    /**
     * Session class instance
     *
     * @var \Illuminate\Session\SessionManager
     */
    protected $session;

    /**
     * Event class instance
     *
     * @var \Illuminate\Contracts\Events\Dispatcher
     */
    protected $event;

    /**
     * Current cart instance
     *
     * @var string
     */
    protected $instance;

    /**
     * Constructor
     *
     * @param \Illuminate\Session\SessionManager $session Session class instance
     * @param \Illuminate\Contracts\Events\Dispatcher $event Event class instance
     */
    public function __construct($session, Dispatcher $event)
    {
        $this->session = $session;
        $this->event = $event;

        $this->instance = 'main';
    }

    /**
     * Set the current cart instance
     *
     * @param  string $instance Cart instance name
     *
     * @throws \Laraverse\Cart\Exceptions\InstanceException
     * @return \Laraverse\Cart\Cart
     */
    public function instance($instance = null)
    {
        if (empty($instance)) {
            throw new Exceptions\InstanceException;
        }

        $this->instance = $instance;

        // Return self so the method is chainable
        return $this;
    }

    /**
     * Add a row to the cart
     *
     * @param string|array $data Item(s) to be added
     *
     * @return boolean
     */
    public function add(array $data)
    {
        // And if it's not only an array, but a multidimensional array, we need to
        // recursively call the add function
        if ($this->is_multi($data)) {
            foreach ($data as $item) {
                $this->add($item);
            }
        }

        $this->isValidItem($data);
        // Fire the cart.add event
        $this->event->fire('cart.adding', $data);

        $result = $this->addRow($data['id'], $data['name'], $data['quantity'], $data['price'], $data['options']);

        // Fire the cart.added event
        $this->event->fire('cart.added', $data);

        return $result;
    }

    /**
     * Update the quantity of one row of the cart
     *
     * @param  string $rowId The rowid of the item you want to update
     * @param  integer|array $attribute New quantity of the item|Array of attributes to update
     *
     * @return boolean
     */
    public function update($rowId, $attribute)
    {
        if (!$this->hasRowId($rowId)) {
            throw new Exceptions\InvalidRowIDException;
        }

        if (is_array($attribute)) {
            // Fire the cart.update event
            $this->event->fire('cart.update', $rowId);

            $result = $this->updateAttribute($rowId, $attribute);

            // Fire the cart.updated event
            $this->event->fire('cart.updated', $rowId);

            return $result;
        }

        // Fire the cart.update event
        $this->event->fire('cart.update', $rowId);

        $result = $this->updateQty($rowId, $attribute);

        // Fire the cart.updated event
        $this->event->fire('cart.updated', $rowId);

        return $result;
    }

    /**
     * Remove a row from the cart
     *
     * @param  string $rowId The rowid of the item
     *
     * @return boolean
     */
    public function remove($rowId)
    {
        if (!$this->hasRowId($rowId)) {
            throw new Exceptions\InvalidRowIDException;
        }

        $cart = $this->getContent();

        // Fire the cart.remove event
        $this->event->fire('cart.remove', $rowId);

        $cart->forget($rowId);

        // Fire the cart.removed event
        $this->event->fire('cart.removed', $rowId);

        return $this->updateCart($cart);
    }

    /**
     * Get a row of the cart by its ID
     *
     * @param  string $rowId The ID of the row to fetch
     *
     * @return \Laraverse\Cart\CartCollection
     */
    public function get($rowId)
    {
        $cart = $this->getContent();

        return ($cart->has($rowId)) ? $cart->get($rowId) : null;
    }

    /**
     * Get the cart content
     *
     * @return \Laraverse\Cart\CartRowCollection
     */
    public function content()
    {
        $cart = $this->getContent();

        return (empty($cart)) ? null : $cart;
    }

    /**
     * Empty the cart
     *
     * @return boolean
     */
    public function destroy()
    {
        // Fire the cart.destroy event
        $this->event->fire('cart.destroy');

        $result = $this->updateCart(null);

        // Fire the cart.destroyed event
        $this->event->fire('cart.destroyed');

        return $result === null;
    }

    /**
     * Get the price total
     *
     * @return float
     */
    public function total()
    {
        $total = 0;
        $cart = $this->getContent();

        if (empty($cart)) {
            return $total;
        }

        foreach ($cart AS $row) {
            $total += $row->subtotal;
        }

        return $total;
    }

    /**
     * Get the number of items in the cart
     *
     * @param  boolean $totalItems Get all the items (when false, will return the number of rows)
     *
     * @return int
     */
    public function count($totalItems = true)
    {
        $cart = $this->getContent();

        if (!$totalItems) {
            return $cart->count();
        }

        $count = 0;

        foreach ($cart AS $row) {
            $count += $row->qty;
        }

        return $count;
    }

    /**
     * Search if the cart has a item
     *
     * @param  array $search An array with the item ID and optional options
     *
     * @return array|boolean
     */
    public function search(array $search)
    {
        if (empty($search)) {
            return false;
        }

        foreach ($this->getContent() as $item) {
            $found = $item->search($search);

            if ($found) {
                $rows[] = $item->rowid;
            }
        }

        return (empty($rows)) ? false : $rows;
    }

    /**
     * Add row to the cart
     *
     * @param string $id Unique ID of the item
     * @param string $name Name of the item
     * @param int $qty Item qty to add to the cart
     * @param float $price Price of one item
     * @param array $options Array of additional options, such as 'size' or 'color'
     */
    protected function addRow($id, $name, $qty, $price, array $options = [])
    {
        if (empty($id) || empty($name) || empty($qty) || !isset($price)) {
            throw new Exceptions\InvalidItemException;
        }

        if (!is_numeric($qty)) {
            throw new Exceptions\InvalidQtyException;
        }

        if (!is_numeric($price)) {
            throw new Exceptions\InvalidPriceException;
        }

        $cart = $this->getContent();

        $rowId = $this->generateRowId($id, $options);

        if ($cart->has($rowId)) {
            $row = $cart->get($rowId);
            $cart = $this->updateRow($rowId, ['qty' => $row->qty + $qty]);
        } else {
            $cart = $this->createRow($rowId, $id, $name, $qty, $price, $options);
        }

        return $this->updateCart($cart);
    }

    /**
     * Generate a unique id for the new row
     *
     * @param  string $id Unique ID of the item
     * @param  array $options Array of additional options, such as 'size' or 'color'
     *
     * @return boolean
     */
    protected function generateRowId($id, $options)
    {
        ksort($options);

        return md5($id . serialize($options));
    }

    /**
     * Check if a rowid exists in the current cart instance
     *
     * @param  string $id Unique ID of the item
     *
     * @return boolean
     */
    protected function hasRowId($rowId)
    {
        return $this->getContent()->has($rowId);
    }

    /**
     * Update the cart
     *
     * @param  \Laraverse\Cart\CartCollection $cart The new cart content
     *
     * @return boolean
     */
    protected function updateCart($cart)
    {
        return $this->session->put($this->getInstance(), $cart);
    }

    /**
     * Get the carts content, if there is no cart content set yet, return a new empty Collection
     *
     * @return \Laraverse\Cart\CartCollection
     */
    protected function getContent()
    {
        $content = ($this->session->has($this->getInstance())) ? $this->session->get($this->getInstance()) : new CartCollection;

        return $content;
    }

    /**
     * Get the current cart instance
     *
     * @return string
     */
    protected function getInstance()
    {
        return 'cart.' . $this->instance;
    }

    /**
     * Update a row if the rowId already exists
     *
     * @param  string $rowId The ID of the row to update
     * @param  integer $qty The quantity to add to the row
     *
     * @return \Laraverse\Cart\CartCollection
     */
    protected function updateRow($rowId, $attributes)
    {
        $cart = $this->getContent();

        $row = $cart->get($rowId);

        foreach ($attributes as $key => $value) {
            if ($key == 'options') {
                $options = $row->options->merge($value);
                $row->put($key, $options);
            } else {
                $row->put($key, $value);
            }
        }

        if (!is_null(array_keys($attributes, ['qty', 'price']))) {
            $row->put('subtotal', $row->qty * $row->price);
        }

        $cart->put($rowId, $row);

        return $cart;
    }

    /**
     * Create a new row Object
     *
     * @param  string $rowId The ID of the new row
     * @param  string $id Unique ID of the item
     * @param  string $name Name of the item
     * @param  int $qty Item qty to add to the cart
     * @param  float $price Price of one item
     * @param  array $options Array of additional options, such as 'size' or 'color'
     *
     * @return \Laraverse\Cart\CartCollection
     */
    protected function createRow($rowId, $id, $name, $qty, $price, $options)
    {
        $cart = $this->getContent();

        $newRow = new CartRowCollection([
            'rowid'    => $rowId,
            'id'       => $id,
            'name'     => $name,
            'qty'      => $qty,
            'price'    => $price,
            'options'  => new CartRowOptionsCollection($options),
            'subtotal' => $qty * $price
        ], $this->associatedModel, $this->associatedModelNamespace);

        $cart->put($rowId, $newRow);

        return $cart;
    }

    /**
     * Update the quantity of a row
     *
     * @param  string $rowId The ID of the row
     * @param  int $qty The qty to add
     *
     * @return \Laraverse\Cart\CartCollection
     */
    protected function updateQty($rowId, $qty)
    {
        if ($qty <= 0) {
            return $this->remove($rowId);
        }

        return $this->updateRow($rowId, ['qty' => $qty]);
    }

    /**
     * Update an attribute of the row
     *
     * @param  string $rowId The ID of the row
     * @param  array $attributes An array of attributes to update
     *
     * @return \Laraverse\Cart\CartCollection
     */
    protected function updateAttribute($rowId, $attributes)
    {
        return $this->updateRow($rowId, $attributes);
    }

    /**
     * Check if the array is a multidimensional array
     *
     * @param  array $array The array to check
     *
     * @return boolean
     */
    protected function is_multi(array $array)
    {
        return is_array(reset($array));
    }

    /**
     * Checks that the item about to be added to the cart is valid.
     *
     * @throws \Laraverse\Cart\Exceptions\InvalidItemException
     * @throws \Laraverse\Cart\Exceptions\InvalidPriceException
     * @throws \Laraverse\Cart\Exceptions\InvalidQtyException
     *
     * @param array $item
     */
    protected function isValidItem(array $item)
    {
        if (empty($item)) {
            throw new \Laraverse\Cart\Exceptions\InvalidItemException;
        }
        if (!is_numeric($item['quantity'])) {
            throw new \Laraverse\Cart\Exceptions\InvalidQtyException;
        }
        if (!is_numeric($item['price'])) {
            throw new \Laraverse\Cart\Exceptions\InvalidPriceException;
        }
    }

}
