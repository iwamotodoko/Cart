<?php namespace Laraverse\Cart\Exceptions;

class InvalidRowID extends \Exception
{
    public $message = 'A rowid was given that does not exist in the cart.';
}
