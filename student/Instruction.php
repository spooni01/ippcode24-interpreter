<?php

namespace IPP\Student;

// External
use DOMDocument;
use DOMElement;

// Internal
use IPP\Student\Exception\InvalidSourceStructureException; // return code 32
use IPP\Student\Argument;


class Instruction 
{

    private int $order;
    private string $opcode;
    private Argument $arg1;
    private Argument $arg2;
    private Argument $arg3;
    private bool $specialNextInstr = false;
    private int $specialNextInstrNum;
    private mixed $operands = [
        ["opCode" => "MOVE", "params" => ["var", "symb"]],
        ["opCode" => "CREATEFRAME", "params" => []],
        ["opCode" => "PUSHFRAME", "params" => []],
        ["opCode" => "POPFRAME", "params" => []],
        ["opCode" => "DEFVAR", "params" => ["var"]],
        ["opCode" => "CALL", "params" => ["label"]],
        ["opCode" => "RETURN", "params" => []],
        ["opCode" => "PUSHS", "params" => ["symb"]],
        ["opCode" => "POPS", "params" => ["var"]],
        ["opCode" => "ADD", "params" => ["var", "symb", "symb"]],
        ["opCode" => "SUB", "params" => ["var", "symb", "symb"]],
        ["opCode" => "MUL", "params" => ["var", "symb", "symb"]],
        ["opCode" => "IDIV", "params" => ["var", "symb", "symb"]],
        ["opCode" => "LT", "params" => ["var", "symb", "symb"]],
        ["opCode" => "GT", "params" => ["var", "symb", "symb"]],
        ["opCode" => "EQ", "params" => ["var", "symb", "symb"]],
        ["opCode" => "AND", "params" => ["var", "symb", "symb"]],
        ["opCode" => "OR", "params" => ["var", "symb", "symb"]],
        ["opCode" => "NOT", "params" => ["var", "symb"]],
        ["opCode" => "INT2CHAR", "params" => ["var", "symb"]],
        ["opCode" => "STRI2INT", "params" => ["var", "symb", "symb"]],
        ["opCode" => "READ", "params" => ["var", "type"]],
        ["opCode" => "WRITE", "params" => ["symb"]],
        ["opCode" => "CONCAT", "params" => ["var", "symb", "symb"]],
        ["opCode" => "STRLEN", "params" => ["var", "symb"]],
        ["opCode" => "GETCHAR", "params" => ["var", "symb", "symb"]],
        ["opCode" => "SETCHAR", "params" => ["var", "symb", "symb"]],
        ["opCode" => "TYPE", "params" => ["var", "symb"]],
        ["opCode" => "LABEL", "params" => ["label"]],
        ["opCode" => "JUMP", "params" => ["label"]],
        ["opCode" => "JUMPIFEQ", "params" => ["label", "symb", "symb"]],
        ["opCode" => "JUMPIFNEQ", "params" => ["label", "symb", "symb"]],
        ["opCode" => "EXIT", "params" => ["symb"]],
        ["opCode" => "DPRINT", "params" => ["symb"]],
        ["opCode" => "BREAK", "params" => []],
    ];


    /*
     *  Constructor
     */
    public function __construct(DOMElement $node, int $order)
    {
        
        $this->order = $order;
        $this->opcode = strtoupper($node->getAttribute("opcode"));

        // Get correct params, that have to be in XML (this function also checks if opcode exist)
        $opcodeParams = $this->getCorrectParams();

        // Extract arguments with type and value
        $cnt = 0;
        foreach ($node->childNodes as $childNode) {
            if ($childNode->nodeType == XML_ELEMENT_NODE) {

                $cnt++;

                // Save to correct variable
                if($cnt == 1) 
                    $this->arg1 = new Argument($childNode->textContent, $childNode->getAttribute("type")); /** @phpstan-ignore-line */ // phpstan was throwing error that function getAttribute() do not exists, but it exists
                else if($cnt == 2)
                    $this->arg2 = new Argument($childNode->textContent, $childNode->getAttribute("type")); /** @phpstan-ignore-line */ // phpstan was throwing error that function getAttribute() do not exists, but it exists
                else if($cnt == 3)
                    $this->arg3 = new Argument($childNode->textContent, $childNode->getAttribute("type")); /** @phpstan-ignore-line */ // phpstan was throwing error that function getAttribute() do not exists, but it exists
                else
                    throw new InvalidSourceStructureException("Maximum number of arguments is 3.");           
            
            }
        }

    }


    /**
     *  Returns params of current opcode
     *  @return array<string>
     */
    private function getCorrectParams(): array 
    {
      
        foreach ($this->operands as $operand) {
            if ($operand["opCode"] == $this->opcode) {
                return $operand["params"];
            }
        }
    
        // Opcode not found, throw error
        throw new InvalidSourceStructureException("Opcode `$this->opcode` do not exists.");           
    
    }

    
    /**
     * Get argument by its number
     */
    public function getArgument(int $position = 1) : Argument {

        if($position == 1) {
            return $this->arg1;
        }
        else if($position == 2) {
            return $this->arg2;
        }
        else {
            return $this->arg3;
        }

    }


    /**
     * Get order
     */
    public function getOrder() : int {

        return $this->order;

    }


    /**
     * Return true if the next instruction will be on another position
     */
    public function isNextPositionSpecial() : bool {

        if($this->specialNextInstr) 
            return true;

        return false;

    }
    

    /**
     * Return true if the next instruction will be on another position
     */
    public function getNextSpecialInstruction() : int {

        return $this->specialNextInstrNum;

    }

}
