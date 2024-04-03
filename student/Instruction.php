<?php

namespace IPP\Student;
use DOMDocument;
use IPP\Core\AbstractInterpreter;
use IPP\Core\Exception\XMLException;
use IPP\Student\Frame;


class Instruction 
{
    
    private string $opcode;
    private Frame $framePtr;

    /*
     *  Constructor
     */
    public function __construct($node, Frame $framePtr)
    {

        $this->checkTagName($node->tagName);
        $this->checkAttributes($node);
        $this->framePtr = $framePtr;

        switch(strtoupper($this->opcode)) {
            case "WRITE":
                $this->execute_write();
                break;
            case "DEFVAR":
                    $this->execute_defvar();
                    break;
            default:
                throw new XMLException("Wrong 'opcode' given.");
        }

    }


    /*
     *  Check if tag name is correct
     */
    private function checkTagName($tagName)
    {

        if($tagName != "instruction")
            throw new XMLException("Node is <$tagName> instead of <instruction>");
        
    }


    /*
     *  Check if attributes are correct
     */
    private function checkAttributes($node)
    {
        
        // Order number
        $order = $node->getAttribute("order");
        if ($order === "" || !ctype_digit($order) || intval($order) <= 0)
            throw new XMLException("'order' attribute is missing or not a positive integer.");

        // Opcode
        $opcode = $node->getAttribute("opcode");
        if ($opcode === "")
            throw new XMLException("'opcode' attribute is missing.");

        $this->opcode = $opcode;

    }


    /*
    *  Functions for intrepreter
    */
    private function execute_write() {

        print("som tu");

    }
    private function execute_defvar() {

        $this->framePtr->addVariable("nameOFVar", "ololo", VariablesTypes::String);
        $this->framePtr->setToTemporaly();
        $this->framePtr->addVariable("nameOFLocalVar", "15", VariablesTypes::Int);
        $this->framePtr->destroyTemporalyFrame();

    }
        

}
