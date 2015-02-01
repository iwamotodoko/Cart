<?php namespace Laraverse\Cart\Tests;

use Laraverse\Cart\Cart;
use Illuminate\Events\Dispatcher;
use Illuminate\Session\Store;
use Illuminate\Session\FileSessionHandler;
use Illuminate\Filesystem\Filesystem;

abstract class Base extends \PHPUnit_Framework_TestCase {

    /*
     * @var \Laraverse\Cart\Cart
     */
    protected $cart;

    public function setup() {
        $sessionHandler = new FileSessionHandler(new Filesystem, __DIR__.'/storage');
        $this->cart = new Cart('test', new Store('cartTest', $sessionHandler),new Dispatcher);
    }
}
