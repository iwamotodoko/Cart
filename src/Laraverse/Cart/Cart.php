<?php namespace Laraverse\Cart;

use Illuminate\Session\Store as SessionStore;
use Illuminate\Contracts\Events\Dispatcher;
use Laraverse\Cart\Collections\Cart as CartCollection;
use Laraverse\Cart\Collections\Row as CartRowCollection;
use Laraverse\Cart\Collections\RowOptions as CartRowOptionsCollection;
use Laraverse\Cart\Exceptions\Instance as InstanceException;
use Laraverse\Cart\Exceptions\InvalidPrice as InvalidPriceException;
use Laraverse\Cart\Exceptions\InvalidQuantity as InvalidQtyException;
use Laraverse\Cart\Exceptions\InvalidItem as InvalidItemException;
use Laraverse\Cart\Exceptions\InvalidRowID as InvalidRowIDException;
use Laraverse\Cart\Exceptions\ItemExists as ItemExistsException;

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
     * @param \Illuminate\Session\Store $session Session class instance
     * @param \Illuminate\Contracts\Events\Dispatcher $event Event class instance
     */
    public function __construct(SessionStore $session, Dispatcher $event)
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
     * @throws \Laraverse\Cart\Exceptions\Instance
     * @return \Laraverse\Cart\Cart
     */
    public function instance($instance = null)
    {
        if (empty($instance)) {
            throw new InstanceException;
        }

        $this->instance = $instance;

        return $this;
    }

    /**
     * Add a row to the cart
     *
     * @param string|array $data Item(s) to be added
     *
     * @return \Laraverse\Cart\Cart
     */
    public function add(array $data)
    {
        // If we have a multidimensional array call this function recursively on each item.
        if ($this->is_multi($data)) {
            foreach ($data as $item) {
                $this->addItem($item);
            }
        } else {
            $this->addItem($data);
        }
        return $this;
    }

    protected function addItem(array $data)
    {
        $this->isValidItem($data);

        $this->event->fire('cart.adding', [$data, $this]);

        $result = $this->addRow($data);

        $this->event->fire('cart.added', [$result, $this]);

        return $result;
    }

    /**
     * Update the quantity of one row of the cart
     *
     * @param  string $rowId The id of the item you want to update
     * @param  array $data Array of attributes to update
     *
     * @throws \Laraverse\Cart\Exceptions\InvalidRowID
     * @return boolean
     */
    public function update($rowId, array $data)
    {
        if (!$this->hasRowId($rowId)) {
            throw new InvalidRowIDException;
        }

        $this->event->fire('cart.updating', [$this->get($rowId), $data, $this]);

        $result = $this->updateAttribute($rowId, $data);

        $this->event->fire('cart.updated', [$result, $this]);

        return $result;
    }

    /**
     * Remove a row from the cart
     *
     * @param  string $rowId The id of the item
     *
     * @throws \Laraverse\Cart\Exceptions\InvalidRowID
     * @return boolean
     */
    public function remove($rowId)
    {
        if (!$this->hasRowId($rowId)) {
            throw new InvalidRowIDException;
        }

        $cart = $this->getContent();
        $item = $this->get($rowId);

        $this->event->fire('cart.removing', [$item, $this]);

        $cart->forget($rowId);

        $this->event->fire('cart.removed', [$item, $this]);

        return $this->updateCart($cart);
    }

    /**
     * Get a row of the cart by its ID
     *
     * @param  string $rowId The ID of the row to fetch
     *
     * @return \Laraverse\Cart\Collections\Row
     */
    public function get($rowId)
    {
        $cart = $this->getContent();

        return ($cart->has($rowId)) ? $cart->get($rowId) : null;
    }

    /**
     * Get the cart content
     *
     * @return \Laraverse\Cart\Cart
     */
    public function content()
    {
        $cart = $this->getContent();

        return (empty($cart)) ? null : $cart;
    }

    /**
     * Empty the cart
     *
     * @return void
     */
    public function destroy()
    {
        $cart = $this;
        $this->event->fire('cart.destroying', $cart);

        $this->session->forget($this->getInstance());

        $this->event->fire('cart.destroyed', $cart);
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

        foreach ($cart as $row) {
            $total += $row->getSubtotal();
        }

        return $total;
    }

    /**
     * Get the number of items in the cart
     *
     * @return int
     */
    public function count()
    {
        $cart = $this->getContent();

        $count = 0;

        foreach ($cart as $row) {
            $count += $row->quantity;
        }

        return $count;
    }

    /**
     * Get the number of rows in the cart.
     *
     * @return int
     */
    public function countRows()
    {
        return $this->getContent()->count();
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
                $rows[] = $item->rowId;
            }
        }

        return (empty($rows)) ? false : $rows;
    }

    /**
     * Add row to the cart
     *
     * @param array $data The data to be added for the row.
     *
     * @throws \Laraverse\Cart\Exceptions\ItemExists
     * @return \Laraverse\Cart\Collections\Cart
     */
    protected function addRow(array $data)
    {
        $cart = $this->getContent();

        $rowId = $this->generateRowId($data);
        $data['rowId'] = $rowId;
        if ($cart->has($rowId)) {
            throw new ItemExistsException;
        } else {
            $cart = $this->createRow($data);
        }

        return $this->updateCart($cart);
    }

    /**
     * Generate a unique id for the new row
     *
     * @param  array $data Data to generate an ID for.
     *
     * @return boolean
     */
    protected function generateRowId(array $data)
    {
        if (isset($data['options'])) {
            ksort($data['options']);
            return sha1($data['id'] . serialize($data['options']));
        }
        return sha1($data['id']);
    }

    /**
     * Check if a rowid exists in the current cart instance
     *
     * @param  string $rowId Unique ID of the item
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
     * @param  \Laraverse\Cart\Collections\Cart $cart The new cart content
     *
     * @return boolean
     */
    protected function updateCart(CartCollection $cart)
    {
        return $this->session->put($this->getInstance(), $cart);
    }

    /**
     * Get the carts content, if there is no cart content set yet, return a new empty Collection
     *
     * @return \Laraverse\Cart\Collections\Cart
     */
    protected function getContent()
    {
        return ($this->session->has($this->getInstance())) ? $this->session->get($this->getInstance()) : new CartCollection;
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
     * @param  array $data The array of data to update the row with.
     *
     * @return \Laraverse\Cart\Collections\Cart
     */
    protected function updateRow($rowId, $data)
    {
        $cart = $this->getContent();

        $row = $cart->get($rowId);

        foreach ($data as $key => $value) {
            if ($key === 'options') {
                $options = $row->options->merge($value);
                $row->put($key, $options);
            } else {
                $row->put($key, $value);
            }
        }

        $cart->put($rowId, $row);

        if (isset($data['quantity']) && (int)$data['quantity'] === 0) {
            $this->remove($rowId);
        }

        return $cart;
    }

    /**
     * Create a new row Object
     *
     * @param  array $data The data to create the row with.
     *
     * @return \Laraverse\Cart\Collections\Cart
     */
    protected function createRow(array $data)
    {
        $cart = $this->getContent();
        $rowId = $this->generateRowId($data);
        $options = $data['options'];
        $data['options'] = new CartRowOptionsCollection($options);

        $newRow = new CartRowCollection($data);

        $cart->put($rowId, $newRow);

        return $cart;
    }

    /**
     * Update an attribute of the row
     *
     * @param  string $rowId The ID of the row
     * @param  array $attributes An array of attributes to update
     *
     * @return \Laraverse\Cart\Collections\Cart
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
     * @throws \Laraverse\Cart\Exceptions\InvalidItem
     * @throws \Laraverse\Cart\Exceptions\InvalidPrice
     * @throws \Laraverse\Cart\Exceptions\InvalidQuantity
     *
     * @param array $item
     */
    protected function isValidItem(array $item)
    {
        if ( !isset($item['id'])
            || !isset($item['quantity'])
            || !isset($item['price'])
        ) {
            throw new InvalidItemException;
        }

        if (!is_numeric($item['quantity'])) {
            throw new InvalidQtyException;
        }
        if (!is_numeric($item['price'])) {
            throw new InvalidPriceException;
        }
    }

}
