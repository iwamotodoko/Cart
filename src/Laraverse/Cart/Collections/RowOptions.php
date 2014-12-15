<?php namespace Laraverse\Cart\Collections;

use Illuminate\Support\Collection;

class RowOptions extends Collection
{

    public function __construct($items)
    {
        parent::__construct($items);
    }

    public function __get($arg)
    {
        if ($this->has($arg)) {
            return $this->get($arg);
        }

        return null;
    }

    public function search($search, $strict = false)
    {
        $found = false;
        foreach ($search as $key => $value) {
            $found = ($this->{$key} === $value) ? true : false;
        }
        return $found;
    }

}
