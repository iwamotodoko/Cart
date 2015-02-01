<?php namespace Laraverse\Cart\Events\Item;

use Laraverse\Cart\Collections\Row;
use Laraverse\Cart\Cart;

class Removed {
    public $item;
    public $cart;

    function __construct(Row $item, Cart $cart)
    {
        $this->item = $item;
        $this->cart = $cart;
    }


}
