<?php

namespace IPP\Student;

// External
use DOMDocument;
use DOMElement;

// Devcontainer
use IPP\Core\AbstractInterpreter;
use IPP\Core\ReturnCode;
use IPP\Core\Exception\XMLException; // return code 31
use IPP\Core\Exception\IntegrationException; // return code 88

// Internal
use IPP\Student\Exception\InvalidSourceStructureException; // return code 32
use IPP\Student\Instruction;


/*
 *  Main interpreter class
 */
class Interpreter extends AbstractInterpreter
{

    private mixed $instructionNumbers = []; // Stores order numbers
    private int $positionOfInstruction = -1; // Stores position of current instruction in $instructionNumbers


    /*
     * Main function
     */
    public function execute(): int
    {
   
        try {

            $dom = $this->source->getDOMDocument(); // Get XML   
            $this->processOrderNumbers($dom); // Process order numbers

            // Loop through instructions by order number
            while ($this->positionOfInstruction < (count($this->instructionNumbers) - 1) ) {

                $this->positionOfInstruction += 1; // Increment to get next instruction, (starts from 0, predefined value is -1)
                $xmlInstr = $this->foundInstructionByOrderNumber($dom->documentElement); // Get instruction by its order number ($this->positionOfInstruction)

                // Parse and execute instruction with order number $this->instructionNumbers
                $instr = new Instruction($xmlInstr, $this->instructionNumbers[$this->positionOfInstruction]);

                // If next instruction is special, set it to its position number
                if($instr->isNextPositionSpecial()) {
                    $this->positionOfInstruction = $instr->getNextSpecialInstruction();
                    $this->positionOfInstruction--; // Instruction function will return correct number, so it must be deacresed by 1 because at the beginning of another while cycle it will by instantly increased by 1
                }
                // TODO1: Create Frame
                // TODO2: Implement operands
                // TODO2: errcodes
                // TODO3: check assignment, make documentation (f.e. check if <program> has correct attributes and values)

            }

        } catch (XMLException $errMsg) {
            $this->stderr->writeString($errMsg);
            exit(ReturnCode::INVALID_XML_ERROR); 
        } catch (InvalidSourceStructureException $errMsg) {
            $this->stderr->writeString($errMsg);
            exit(ReturnCode::INVALID_SOURCE_STRUCTURE); 
        } catch (IntegrationException $errMsg) {
            $this->stderr->writeString($errMsg);
            exit(ReturnCode::INTEGRATION_ERROR); 
        }

        return 0;

    }


    /*
     *  Get order numbers and sort it
     */
    private function processOrderNumbers(DOMDocument $dom) : void
    {

        // Retrieve order numbers
        $rootNode = $dom->documentElement;
        foreach($rootNode->childNodes as $instr) {
            if ($instr->nodeType === XML_ELEMENT_NODE) {
                
                // Check if numbers are bigger or equal 0
                $orderNum = $instr->getAttribute("order"); /** @phpstan-ignore-line */ // phpstan was throwing error that function getAttribute() do not exists, but it exists

                if (!ctype_digit($orderNum)) 
                    throw new InvalidSourceStructureException("Parameter `order` must be integer bigger or equal than zero.");     
                else
                    array_push($this->instructionNumbers, $orderNum); 
            
            }
        }

        // Sort array to go from lowest to highest
        sort($this->instructionNumbers);

        // Check if numbers are bigger or equal 0
        if ($this->instructionNumbers[0] <= 0) 
            throw new InvalidSourceStructureException("Numbers must be bigger or equal zero.");     

        // Check if there is duplicated order number
        $orderCounts = array_count_values($this->instructionNumbers);
        foreach ($rootNode->childNodes as $child) {
            
            if ($child->nodeType === XML_ELEMENT_NODE && $child->tagName === 'instruction') { /** @phpstan-ignore-line */ // phpstan was throwing error that $tagName is undefined, but it exists
                $order = (int) $child->getAttribute('order'); /** @phpstan-ignore-line */ // phpstan was throwing error that function getAttribute() do not exists, but it exists
                if (isset($orderCounts[$order]) && $orderCounts[$order] > 1) 
                    throw new InvalidSourceStructureException("There is duplicated order number: $order.");           
            }
            
        }

    }


    /**
     *  Get instruction by its order number ($this->positionOfInstruction)
     *  @param DOMElement $rootNode
     */
    private function foundInstructionByOrderNumber($rootNode) : DOMElement
    {
        
        foreach ($rootNode->childNodes as $instr) {

            if ($instr->nodeType == XML_ELEMENT_NODE && $instr->tagName == 'instruction') { /** @phpstan-ignore-line */ // phpstan was throwing error that $tagName do not exists, but it exists
                
                if ((int)$instr->getAttribute('order') == $this->instructionNumbers[$this->positionOfInstruction]) /** @phpstan-ignore-line */ // phpstan was throwing error that function getAttribute() do not exists, but it exists
                    return $instr; /** @phpstan-ignore-line */ // phpstan was throwing error that this returns DOMNode, but it returns DOMElement

            }

        }

        // Just for phpstan, do not need to check if node with order number exists because in
        // $this->instructionNumbers are only numbers with defined order. 
        $tmp = new DOMElement("x");
        return $tmp;
    
    }


}
