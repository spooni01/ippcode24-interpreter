<?php

namespace IPP\Student\ObjectsContainer;

// Internal
use IPP\Student\Exception\SemanticException; // return code 52
use IPP\Student\Exception\VariableAccessException; // return code 54


class Frame
{

    private mixed $variables = [];
    public mixed $types = [];


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
            throw new SemanticException("Variable '$name' does not exist in this frame.");
        }

        return $this->variables[$name];

    }


    /**
     *  Get the type of the variable with the given name
     */
    public function getType(string $name) : mixed
    {

        if (!$this->hasVariable($name)) {
            throw new SemanticException("Variable '$name' does not exist in this frame.");
        }

        return $this->types[$name];

    }


    /**
     *  Define a new variable in the frame
     */
    public function defineVariable(string $name) : void
    {

        if ($this->hasVariable($name)) {
            throw new SemanticException("Variable '$name' is already defined in this frame.");
        }

        $this->variables[$name] = null;

    }


    /**
     *  Set the value of the variable with the given name
     */
    public function setVariable(string $name, mixed $value, string $type) : void
    {

        if (!$this->hasVariable($name)) {
            throw new VariableAccessException("Variable '$name' does not exist in this frame.");
        }

        $this->variables[$name] = $value;
        $this->types[$name] = $type;

    }


    /**
     *  Delete the variable with the given name from the frame
     */
    public function deleteVariable(string $name) : void
    {

        if (!$this->hasVariable($name)) {
            throw new SemanticException("Variable '$name' does not exist in this frame.");
        }

        unset($this->variables[$name]);
        unset($this->types[$name]);

    }

}

?>