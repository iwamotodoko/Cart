<?php namespace Laraverse\Cart\Exceptions;

class ItemExists extends \Exception
{
    public $message = 'An item was given to be added to the cart that already exists.';
}
