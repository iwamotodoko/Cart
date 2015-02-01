<?php namespace Laraverse\Cart\Events\Cart;

use Laraverse\Cart\Collections\Cart;

class Created {
    public $cart;

    public function __construct(Cart $cart) {
        $this->cart = $cart;
    }
}
