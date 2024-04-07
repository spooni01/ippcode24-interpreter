<?php

namespace IPP\Student;

// Internal
use IPP\Student\Exception\InvalidSourceStructureException; // return code 32


class Frame
{

    private mixed $variables = [];


    /**
     *  Get the number of variables in the frame
     */
    public function size(): int
    {

        return count($this->variables);

    }


    /**
     *  Check if the frame has a variable with the given name
     */
    public function hasVariable(string $name): bool
    {

        return array_key_exists($name, $this->variables);

    }


    /**
     *  Get the value of the variable with the given name
     */
    public function getVariable(string $name) : mixed
    {

        if (!$this->hasVariable($name)) {
            throw new InvalidSourceStructureException("Variable '$name' does not exist in this frame.");
        }

        return $this->variables[$name];

    }


    /**
     *  Define a new variable in the frame
     */
    public function defineVariable(string $name) : void
    {

        if ($this->hasVariable($name)) {
            throw new InvalidSourceStructureException("Variable '$name' is already defined in this frame.");
        }

        $this->variables[$name] = null;

    }


    /**
     *  Set the value of the variable with the given name
     */
    public function setVariable(string $name, mixed $value) : void
    {

        if (!$this->hasVariable($name)) {
            throw new InvalidSourceStructureException("Variable '$name' does not exist in this frame.");
        }

        $this->variables[$name] = $value;

    }


    /**
     *  Delete the variable with the given name from the frame
     */
    public function deleteVariable(string $name) : void
    {

        if (!$this->hasVariable($name)) {
            throw new InvalidSourceStructureException("Variable '$name' does not exist in this frame.");
        }

        unset($this->variables[$name]);

    }

}

?>