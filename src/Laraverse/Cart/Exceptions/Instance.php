<?php namespace Laraverse\Cart\Exceptions;

class Instance extends \Exception
{
    public $message = 'An instance name was not given to the cart.';
}
