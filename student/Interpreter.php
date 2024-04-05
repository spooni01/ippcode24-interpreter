<?php

namespace IPP\Student;

// External
use DOMDocument;

// Devcontainer
use IPP\Core\AbstractInterpreter;
use IPP\Core\ReturnCode;
use IPP\Core\Exception\XMLException; // return code 31
use IPP\Core\Exception\IntegrationException; // return code 88

// Internal
use IPP\Student\Exception\InvalidSourceStructureException; // return code 32


/*
 *  Main interpreter class
 */
class Interpreter extends AbstractInterpreter
{

    private mixed $instructionNumbers = []; // Stores order numbers
    private int $positionOfInstructions = 0; // Stores position of current instruction in $instructionNumbers


    /*
     * Main function
     */
    public function execute(): int
    {
   
        try
        {

            $dom = $this->source->getDOMDocument(); // Get XML   
            $this->processOrderNumbers($dom); // Process order numbers

            // Loop through instructions by order number
            while ($this->positionOfInstructions < count($this->instructionNumbers)) {

                // TODO2: find instruction by its number and parse it also with arguments
                // TODO3: Create Queue, Stack, Frame
                // TODO4: Implement operands

                $this->positionOfInstructions += 1;

            }

        }
        catch (XMLException $errMsg) 
        {

            $this->stderr->writeString($errMsg);
            exit(ReturnCode::INVALID_XML_ERROR); 

        }
        catch (InvalidSourceStructureException $errMsg) 
        {

            $this->stderr->writeString($errMsg);
            exit(ReturnCode::INVALID_SOURCE_STRUCTURE); 

        }
        catch (IntegrationException $errMsg) 
        {

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


}
