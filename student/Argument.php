<?php

namespace IPP\Student;

// External
use DOMDocument;
use IPP\Core\AbstractInterpreter;

// Internal
use IPP\Student\Exception\InvalidSourceStructureException; // return code 32


class Argument 
{
    
    private mixed $type;
    private mixed $value;
    private string $argPattern;

    /*
     *  Constructor
     */
    public function __construct(string $unprocessedOperand, string $argPattern)
    {

        $this->argPattern = $argPattern;

        if ($argPattern === "var") {
            $this->processVar($unprocessedOperand);
        } elseif ($argPattern === "symb") {
            $this->processSymb($unprocessedOperand);
        } elseif ($argPattern === "label") {
            $this->processLabel($unprocessedOperand);
        } elseif ($argPattern === "type") {
            $this->processType($unprocessedOperand);
        } else {
            throw new InvalidSourceStructureException("Invalid argument pattern: $argPattern");
        }

    }
        

    public function separateAndSave(string $unprocessedOperand) : void {
        $parts = explode('@', $unprocessedOperand);

        if (count($parts) == 2 && preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $parts[1])) {
            $this->type = $parts[0];
            $this->value = $parts[1];
        } else {
            throw new InvalidSourceStructureException("Argument error.");
        }
    }

    public function getType() : mixed {
        return $this->type;
    }

    public function getValue() : mixed {
        return $this->value;
    }

    public function getArgPattern() : string{
        return $this->argPattern;
    }


    public function getSecondValue(string $unprocessedOperand, string $typeOfConstant) : string {
        $secondValue = "";

        $parts = explode('@', $unprocessedOperand, 2);
        if (count($parts) > 1) {
            $secondValue = $parts[1];
        }

        if ($typeOfConstant == "bool" && ($secondValue != "true" && $secondValue != "false")) {
            throw new InvalidSourceStructureException("Argument error.");
        }

        return $secondValue;
    }


    public function processVar(string $unprocessedOperand) : void {
        if (!preg_match('/^(LF|GF|TF)@[a-zA-Z_][a-zA-Z0-9_]*$/', $unprocessedOperand)) {
            throw new InvalidSourceStructureException("Argument  error.");
        } else {
            $this->type = "var";
            $this->value = $unprocessedOperand;
        }
    }


    public function processSymb(string $unprocessedOperand) : void {
        if (preg_match('/^(LF|GF|TF)@[a-zA-Z_][a-zA-Z0-9_]*$/', $unprocessedOperand)) {
            $this->type = "var";
            $this->value = $unprocessedOperand;
        } elseif (preg_match('/^bool@(true|false)/', $unprocessedOperand)) {
            $this->type = "bool";
            $this->value = $this->getSecondValue($unprocessedOperand, "bool");
        } elseif (preg_match('/^string@.*$/', $unprocessedOperand)) {
            $this->type = "string";
            $this->value = $this->getSecondValue($unprocessedOperand, "string");
        } elseif (preg_match('/^nil@nil/', $unprocessedOperand)) {
            $this->type = "nil";
            $this->value = "nil";
        } elseif (preg_match('/^int@(-?0x[0-9a-fA-F]+|-?0o[0-7]+|-?\d+)$/', $unprocessedOperand)) {
            $this->type = "int";
            $this->value = $this->getSecondValue($unprocessedOperand, "int");
        } else {
            throw new InvalidSourceStructureException("Argument error.");
        }
    }


    public function processLabel(string $unprocessedOperand) : void {
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $unprocessedOperand)) {
            throw new InvalidSourceStructureException("Argument error.");
        } else {
            $this->type = "label";
            $this->value = $unprocessedOperand;
        }
    }


    public function processType(string $unprocessedOperand) : void {
        if (!in_array($unprocessedOperand, ["int", "bool", "string", "nil", "label", "type", "var"])) {
            throw new InvalidSourceStructureException("Argument error.");
        } else {
            $this->type = "type";
            $this->value = $unprocessedOperand;
        }
    }


}
