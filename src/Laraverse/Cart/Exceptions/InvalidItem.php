<?php namespace Laraverse\Cart\Exceptions;

class InvalidItem extends \Exception
{
    public $message = 'An item given to the cart had invalid data.';
}
