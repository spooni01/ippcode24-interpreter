<?php

namespace IPP\Student;

use DOMDocument;
use DOMXPath;
use IPP\Core\AbstractInterpreter;
use IPP\Core\Exception\XMLException;
use IPP\Student\Instruction;
use IPP\Student\Frame;

/* Definition of enums */
enum Frames : string {
    case Global = "GF";
    case Temporaly = "TF";
    case Local = "LF";
}
enum VariablesTypes : string {
    case Int = "Int";
    case String = "String";
}

/* Main class */
class Interpreter extends AbstractInterpreter
{

    private array $instrOrder = [];
    private Frame $frame;

    /*
     * Main function
     */
    public function execute(): int
    {

        $this->frame = new Frame;
        $dom = $this->source->getDOMDocument(); // Get XML        
        $val = $this->input->readString();  // Read input

        // Check header
        $rootNode = $dom->documentElement;
        if($rootNode == false || $rootNode->nodeName != "program" || $rootNode->getAttribute("language") != "IPPcode24") {
            $this->stderr->writeString("Error: wrong header of XML code.");
            exit(31);    
        }

        // Get order numbers and sort it
        foreach($rootNode->childNodes as $instr) {
            if ($instr->nodeType === XML_ELEMENT_NODE) {
                array_push($this->instrOrder, $instr->getAttributeNode("order")->nodeValue);
            }
        }
        sort($this->instrOrder);

        // Loop through instructions by order number
        foreach($this->instrOrder as $instrNum) { 
            $xpath = new DOMXPath($dom);
            $expression = "//instruction[@order='$instrNum']";
            $xmlInstr = $xpath->query($expression)->item(0);

            try
            {

                $instruction = new Instruction($xmlInstr, $this->frame);

            }
            catch (XMLException $errMsg)
            {
                $this->stderr->writeString($errMsg);
                exit(31);  
            }
        }

        $this->frame->print();
    
        return 0;

    }

}
