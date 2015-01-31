<?php namespace Laraverse\Cart\Events\Item;

use Laraverse\Cart\Cart;
use Laraverse\Cart\Collections\Row;

class Created {
    public $item;
    public $cart;

    function __construct(Row $item, Cart $cart)
    {
        $this->item = $item;
        $this->cart = $cart;
    }

}
