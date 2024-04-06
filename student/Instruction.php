<?php

namespace IPP\Student;

// External
use DOMDocument;
use DOMElement;

// Internal
use IPP\Student\Exception\InvalidSourceStructureException; // return code 32


class Instruction 
{

    private int $order;
    private string $opcode;
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
        $this->opcode = strtoupper($node->getAttribute('opcode'));

        // Get correct params, that have to be in XML (this function also checks if opcode exist)
        $opcodeParams = $this->getCorrectParams();

        /***/ 
        //todo arguments
        // Extract arguments with type and value
        $arguments = [];
        foreach ($node->childNodes as $childNode) {
            if ($childNode->nodeType === XML_ELEMENT_NODE) {
                $type = $childNode->getAttribute('type'); /** @phpstan-ignore-line */ // phpstan was throwing error that function getAttribute() do not exists, but it exists
                $value = $childNode->textContent;
                $arguments[] = ['type' => $type, 'value' => $value];
            }
        }

        // Print extracted data
        print("Order: " . $this->order . "\n");
        print("Opcode: " . $this->opcode . "\n");
        print( "Arguments:\n");
        foreach ($arguments as $argument) {
            print( "- Type: " . $argument['type'] . ", Value: " . $argument['value'] . "\n");
        }


    }


    /**
     *  Returns params of current opcode
     *  @return array<string>
     */
    private function getCorrectParams(): array 
    {
      
        foreach ($this->operands as $operand) {
            if ($operand['opCode'] == $this->opcode) {
                return $operand['params'];
            }
        }
    
        // Opcode not found, throw error
        throw new InvalidSourceStructureException("Opcode `$this->opcode` do not exists.");           
    
    }


}
