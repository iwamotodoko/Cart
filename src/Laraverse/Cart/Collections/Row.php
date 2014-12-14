<?php namespace Laraverse\Cart\Collections;

use Illuminate\Support\Collection;

class Row extends Collection
{

    /**
     * Constructor for the CartRowCollection
     *
     * @param array $items
     */
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
            if ($key === 'options') {
                $found = $this->{$key}->search($value);
            } else {
                $found = ($this->{$key} === $value) ? true : false;
            }
        }
        return $found;
    }

}
