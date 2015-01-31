<?php namespace Laraverse\Cart\Exceptions;

class InvalidQuantity extends \Exception
{
    public $message = 'An invalid quantity was given for a cart item.';
}
