<?php namespace Laraverse\Cart\Events\Item;

use Laraverse\Cart\Cart;

class Creating {
    public $data;
    public $cart;

    function __construct(array $data, Cart $cart)
    {
        $this->data = $data;
        $this->cart = $cart;
    }


}
