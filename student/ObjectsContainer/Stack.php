<?php

namespace IPP\Student;

// Internal
use IPP\Student\Exception\InvalidSourceStructureException; // return code 32


class Stack
{

    private mixed $items = [];

    /**
     *  Push an item onto the top of the stack
     */
    public function push(mixed $item) : void
    {

        array_unshift($this->items, $item);

    }


    /**
     *  Pop an item from the top of the stack
     */
    public function pop() : mixed
    {

        if ($this->isEmpty()) {
            throw new InvalidSourceStructureException("Stack is empty.");
        }

        return array_shift($this->items);

    }


    /**
     *  Check if the stack is empty
     */
    public function isEmpty(): bool
    {

        return empty($this->items);

    }


    /**
     *  Get the size of the stack
     */
    public function size(): int
    {

        return count($this->items);

    }


    /**
     *  View the item at the top of the stack without removing it
     */
    public function peek() : mixed
    {

        if ($this->isEmpty()) {
            throw new InvalidSourceStructureException("Stack is empty.");
        }

        return $this->items[0];

    }


    /**
     *  Clear the stack
     */
    public function clear() : void
    {

        $this->items = [];

    }

  
}

?>