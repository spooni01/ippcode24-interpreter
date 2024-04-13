<?php

namespace IPP\Student;

// External
use DOMDocument;
use DOMElement;

// Devcontainer
use IPP\Core\AbstractInterpreter;
use IPP\Core\ReturnCode;
use IPP\Core\Interface\InputReader;
use IPP\Core\Interface\OutputWriter;
use IPP\Core\Exception\XMLException; // return code 31
use IPP\Core\Exception\IntegrationException; // return code 88

// Internal
use IPP\Student\Exception\InvalidSourceStructureException; // return code 32
use IPP\Student\Exception\SemanticException; // return code 52
use IPP\Student\Exception\OperandTypeException; // return code 53
use IPP\Student\Exception\VariableAccessException; // return code 54
use IPP\Student\Exception\FrameAccessException; // return code 55
use IPP\Student\Exception\ValueException; // return code 56
use IPP\Student\Exception\OperandValueException; // return code 57
use IPP\Student\Exception\StringOperationException; // return code 58
use IPP\Student\Exception\ExitProgramException; // for opcode EXIT
use IPP\Student\Instruction;
use IPP\Student\ObjectsContainer\Frame;
use IPP\Student\ObjectsContainer\Stack;
use IPP\Student\Argument;


/*
 *  Main interpreter class
 */
class Interpreter extends AbstractInterpreter
{

    public InputReader $input; // Public to use it in class Instruction as pointer
    public OutputWriter $stdout; // Public to use it in class Instruction as pointer
    public OutputWriter $stderr; // Public to use it in class Instruction as pointer
    private mixed $instructionNumbers = []; // Stores order numbers
    public int $positionOfInstruction = -1; // Stores position of current instruction in $instructionNumbers
    public mixed $frames = []; // Stores frames, initialization of frames in function initFrames()
    public Stack $framesStack; // Stores frames
    public Stack $dataStack; // Stores frames
    public Stack $callStack; // Stores data of calling functions/labels
    public mixed $labels = []; // Stores defined labels ("name" => "position")

    /*
     * Main function
     */
    public function execute(): int
    {
   
        try {

            $dom = $this->source->getDOMDocument(); // Get XML   
            $this->processOrderNumbers($dom); // Process order numbers
            $this->initFrames();

            // Loop through instructions by order number
            while ($this->positionOfInstruction < (count($this->instructionNumbers) - 1) ) {

                $this->positionOfInstruction++; // In1rement to get next instruction, (starts from 0, predefined value is -1)

                $xmlInstr = $this->foundInstructionByOrderNumber($dom->documentElement); // Get instruction by its order number ($this->positionOfInstruction)
                // Parse and execute instruction with order number $this->instructionNumbers
                $instr = new Instruction($xmlInstr, (int)str_replace(" ", "", $this->instructionNumbers[$this->positionOfInstruction]), $this);

                // If next instruction is special, set it to its position number
                if($instr->isNextPositionSpecial()) {
                    $this->positionOfInstruction = $instr->getNextSpecialInstruction();
                    $this->positionOfInstruction -= 1; // Instruction function will return correct number, so it must be deacresed by 1 because at the beginning of another while cycle it will by instantly increased by 1
                    
                    if($this->positionOfInstruction > (count($this->instructionNumbers))) {
                        break;
                    }

                     // Get position of key
                    $this->positionOfInstruction = array_search($this->positionOfInstruction+1, $this->instructionNumbers);

                }  
                
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
        } catch (SemanticException $errMsg) {
            $this->stderr->writeString($errMsg);
            exit(ReturnCode::SEMANTIC_ERROR); 
        } catch (VariableAccessException $errMsg) {
            $this->stderr->writeString($errMsg);
            exit(ReturnCode::VARIABLE_ACCESS_ERROR); 
        } catch (FrameAccessException $errMsg) {
            $this->stderr->writeString($errMsg);
            exit(ReturnCode::FRAME_ACCESS_ERROR); 
        } catch (OperandTypeException $errMsg) {
            $this->stderr->writeString($errMsg);
            exit(ReturnCode::OPERAND_TYPE_ERROR); 
        } catch (OperandValueException $errMsg) {
            $this->stderr->writeString($errMsg);
            exit(ReturnCode::OPERAND_VALUE_ERROR); 
        } catch (ExitProgramException $errMsg) {
            exit($errMsg->returnCode); 
        } catch (StringOperationException $errMsg) {
            exit(ReturnCode::STRING_OPERATION_ERROR); 
        } catch (ValueException $errMsg) {
            exit(ReturnCode::VALUE_ERROR); 
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
                $orderNum = str_replace(" ", "", $instr->getAttribute("order")); /** @phpstan-ignore-line */ // phpstan was throwing error that function getAttribute() do not exists, but it exists
                if (!ctype_digit($orderNum)) 
                    throw new InvalidSourceStructureException("Parameter `order` must be integer bigger or equal than zero.");     
                else
                    array_push($this->instructionNumbers, $orderNum); 
                
                // Save label name with order num
                if(strtoupper((string)$instr->getAttribute('opcode')) == "LABEL") { /** @phpstan-ignore-line */ // PHP STAN writes that getAttribute is undefined, but it is defined
                    $tmpInstr = new Instruction($instr, (int)$orderNum, $this); /** @phpstan-ignore-line */ // $instr is DOMElement

                    if (isset($this->labels[$tmpInstr->getArg1()->getValue()])) 
                        throw new SemanticException("Label already exists.");
                    else {
                        $this->labels[$tmpInstr->getArg1()->getValue()] = $orderNum;
                    }
                }
            
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
                
                if ((int)str_replace(" ", "", $instr->getAttribute('order')) == $this->instructionNumbers[$this->positionOfInstruction]) /** @phpstan-ignore-line */ // phpstan was throwing error that function getAttribute() do not exists, but it exists
                    return $instr; /** @phpstan-ignore-line */ // phpstan was throwing error that this returns DOMNode, but it returns DOMElement
                
 
            }

        }

        // Just for phpstan, do not need to check if node with order number exists because in
        // $this->instructionNumbers are only numbers with defined order. 
        $tmp = new DOMElement("x");
        return $tmp;
    
    }


    /**
     *  Init of frames
     */
    private function initFrames() : void
    {

        $this->framesStack = new Stack();
        $this->callStack = new Stack();
        $this->dataStack = new Stack();
        
        $global = new Frame();

        $this->frames = [
            "GF" => $global,
            "TF" => NULL
        ];

    }


}
