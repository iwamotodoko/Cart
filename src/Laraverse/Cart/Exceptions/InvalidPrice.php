<?php namespace Laraverse\Cart\Exceptions;

class InvalidPrice extends \Exception
{
    public $message = 'An invalid price was given for a cart item.';
}
