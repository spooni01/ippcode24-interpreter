<?php

namespace IPP\Student;

// Internal
use IPP\Student\Exception\InvalidSourceStructureException; // return code 32


class Queue
{

    private mixed $items = [];


    /**
     *  Add an item to the end of the queue
     */
    public function enqueue(mixed $item) : void
    {

        $this->items[] = $item;

    }


    /**
     *  Remove an item from the front of the queue
     */
    public function dequeue() : mixed
    {

        if ($this->isEmpty()) {
            throw new InvalidSourceStructureException("The queue is empty.");
        }

        return array_shift($this->items);

    }


    /**
     *  Check if the queue is empty
     */
    public function isEmpty(): bool
    {

        return empty($this->items);

    }


    /**
     *  Get the size of the queue
     */
    public function size(): int
    {

        return count($this->items);

    }


    /**
     *  View the item at the front of the queue without removing it
     */
    public function peek() : mixed
    {

        if ($this->isEmpty()) {
            throw new InvalidSourceStructureException("The queue is empty.");
        }

        return $this->items[0];

    }


    /**
     *  Clear the queue
     */
    public function clear() : void
    {

        $this->items = [];

    }


}

?>