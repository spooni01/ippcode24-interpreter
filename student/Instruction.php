<?php

namespace IPP\Student;

// External
use DOMDocument;
use DOMElement;

// Devcontainer
use IPP\Core\AbstractInterpreter;

// Internal
use IPP\Student\Exception\InvalidSourceStructureException; // return code 32
use IPP\Student\Exception\SemanticException; // return code 52
use IPP\Student\Exception\OperandTypeException; // return code 53
use IPP\Student\Exception\OperandValueException; // return code 57
use IPP\Student\Exception\ExitProgramException; // for opcode EXIT
use IPP\Student\Argument;
use IPP\Student\ObjectsContainer\Frame;


class Instruction 
{

    private int $order;
    private string $opcode;
    public Interpreter $interpreterPtr; // To use stdout, stderr and input Interpreter special functions
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
    public function __construct(DOMElement $node, int $order, Interpreter $interpreterPtr)
    {
        
        // Setters
        $this->order = $order;
        $this->opcode = strtoupper($node->getAttribute("opcode"));
        $this->interpreterPtr = $interpreterPtr;

        // Get correct params, that have to be in XML (this function also checks if opcode exist)
        $opcodeParams = $this->getCorrectParams();

        // Extract arguments with type and value
        $cnt = 0;
        $numOfCorrectArguments = count($opcodeParams);
        foreach ($node->childNodes as $childNode) {
            if ($childNode->nodeType == XML_ELEMENT_NODE) {

                $cnt++;
                $tagName = $childNode->tagName; /** @phpstan-ignore-line */ // PHP STAN writes that tagName is undefined, but it is defined

                // Save to correct variable
                if($cnt == 1 && $numOfCorrectArguments >= 1) 
                    if ($tagName == "arg1")
                        $this->arg1 = new Argument($childNode->textContent, $childNode->getAttribute("type"), $opcodeParams[0], $this); /** @phpstan-ignore-line */ // phpstan was throwing error that function getAttribute() do not exists, but it exists
                    else if  ($tagName == "arg2")
                        $this->arg2 = new Argument($childNode->textContent, $childNode->getAttribute("type"), $opcodeParams[1], $this); /** @phpstan-ignore-line */ // phpstan was throwing error that function getAttribute() do not exists, but it exists
                    else if  ($tagName == "arg3")
                        $this->arg3 = new Argument($childNode->textContent, $childNode->getAttribute("type"), $opcodeParams[2], $this); /** @phpstan-ignore-line */ // phpstan was throwing error that function getAttribute() do not exists, but it exists
                    else
                        throw new InvalidSourceStructureException("Invalid argument tag");
                else if($cnt == 2 && $numOfCorrectArguments >= 2)
                    if ($tagName == "arg1")
                        $this->arg1 = new Argument($childNode->textContent, $childNode->getAttribute("type"), $opcodeParams[0], $this); /** @phpstan-ignore-line */ // phpstan was throwing error that function getAttribute() do not exists, but it exists
                    else if  ($tagName == "arg2")
                        $this->arg2 = new Argument($childNode->textContent, $childNode->getAttribute("type"), $opcodeParams[1], $this); /** @phpstan-ignore-line */ // phpstan was throwing error that function getAttribute() do not exists, but it exists
                    else if  ($tagName == "arg3")
                        $this->arg3 = new Argument($childNode->textContent, $childNode->getAttribute("type"), $opcodeParams[2], $this); /** @phpstan-ignore-line */ // phpstan was throwing error that function getAttribute() do not exists, but it exists
                    else
                        throw new InvalidSourceStructureException("Invalid argument tag");        
                else if($cnt == 3 && $numOfCorrectArguments >= 3) 
                    if ($tagName == "arg1")
                        $this->arg1 = new Argument($childNode->textContent, $childNode->getAttribute("type"), $opcodeParams[0], $this); /** @phpstan-ignore-line */ // phpstan was throwing error that function getAttribute() do not exists, but it exists
                    else if  ($tagName == "arg2")
                        $this->arg2 = new Argument($childNode->textContent, $childNode->getAttribute("type"), $opcodeParams[1], $this); /** @phpstan-ignore-line */ // phpstan was throwing error that function getAttribute() do not exists, but it exists
                    else if  ($tagName == "arg3")
                        $this->arg3 = new Argument($childNode->textContent, $childNode->getAttribute("type"), $opcodeParams[2], $this); /** @phpstan-ignore-line */ // phpstan was throwing error that function getAttribute() do not exists, but it exists
                    else
                        throw new InvalidSourceStructureException("Invalid argument tag");
                else
                    throw new InvalidSourceStructureException("Maximum number of arguments for `$this->opcode` is $numOfCorrectArguments.");           
            
            }
        }

        // Choose correct execute function
        switch ($this->opcode) {
            case "MOVE":
                $this->execute_move();
                break;
            case "CREATEFRAME":
                $this->execute_createframe();
                break;
            case "PUSHFRAME":
                $this->execute_pushframe();
                break;
            case "POPFRAME":
                $this->execute_popframe();
                break;
            case "DEFVAR":
                $this->execute_defvar();
                break;
            case "CALL":
                $this->execute_call();
                break;
            case "RETURN":
                // Handle RETURN operation
                break;
            case "PUSHS":
                // Handle PUSHS operation
                break;
            case "POPS":
                // Handle POPS operation
                break;
            case "ADD":
                $this->execute_add();
                break;
            case "SUB":
                $this->execute_sub();
                break;
            case "MUL":
                $this->execute_mul();
                break;
            case "IDIV":
                $this->execute_idiv();
                break;
            case "LT":
                $this->execute_lt();
                break;
            case "GT":
                $this->execute_gt();
                break;
            case "EQ":
                $this->execute_eq();
                break;
            case "AND":
                $this->execute_and();
                break;
            case "OR":
                $this->execute_or();
                break;
            case "NOT":
                $this->execute_not();
                break;
            case "INT2CHAR":
                // Handle INT2CHAR operation
                break;
            case "STRI2INT":
                // Handle STRI2INT operation
                break;
            case "READ":
                $this->execute_read();
                break;
            case "WRITE":
                $this->execute_write();
                break;
            case "CONCAT":
                $this->execute_concat();
                break;
            case "STRLEN":
                $this->execute_strlen();
                break;
            case "GETCHAR":
                // Handle GETCHAR operation
                break;
            case "SETCHAR":
                // Handle SETCHAR operation
                break;
            case "TYPE":
                // Handle TYPE operation
                break;
            case "LABEL":
                $this->execute_label();
                break;
            case "JUMP":
                $this->execute_jump();
                break;
            case "JUMPIFEQ":
                $this->execute_jumpifeq();
                break;
            case "JUMPIFNEQ":
                // Handle JUMPIFNEQ operation
                break;
            case "EXIT":
                $this->execute_exit();
                break;
            case "DPRINT":
                // Handle DPRINT operation
                break;
            case "BREAK":
                // Handle BREAK operation
                break;      
            default:
                throw new InvalidSourceStructureException("Wrong opcode `$this->opcode` given.");             
        }

    }


    /**
     *  Get arg1
     */
    public function getArg1() : Argument {
        
        return $this->arg1;

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
    public function getArgument(int $position = 1) : Argument
    {

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
    public function getOrder() : int
    {

        return $this->order;

    }


    /**
     * Return true if the next instruction will be on another position
     */
    public function isNextPositionSpecial() : bool
    {

        if($this->specialNextInstr) 
            return true;

        return false;

    }
    

    /**
     * Return true if the next instruction will be on another position
     */
    public function getNextSpecialInstruction() : int
    {

        return $this->specialNextInstrNum;

    }


    /**
     *  Convert ASCII chars to string
     */
    function replaceAsciiChars(string $string) : string {

        $string = preg_replace_callback('/\\\\([0-9]{3})/', function($matches) {
            return chr((int)$matches[1]);
        }, $string);
    
        return $string;
    
    }


    /**
     * Execude WRITE
     */
    private function execute_write() : void
    {

        $str = "";

        // Choose by type
        if($this->arg1->getType() == "bool") {
            $str = $this->arg1->getValue();
        }
        else if($this->arg1->getType() == "nil") {
            $str = "";
        }
        else if($this->arg1->getType() == "var") {

            // Choose frame
            if($this->arg1->getFirstValue() == "GF") {
                $str = $this->interpreterPtr->frames["GF"]->getVariable($this->arg1->getSecondValue());
            }
            else if($this->arg1->getFirstValue() == "TF") {
                $str = $this->interpreterPtr->frames["TF"]->getVariable($this->arg1->getSecondValue()); 
            }
            else if($this->arg1->getFirstValue() == "LF") {
                $str = $this->interpreterPtr->framesStack->peek()->getVariable($this->arg1->getSecondValue());
            }

        }
        else {
            $str = $this->arg1->getValue();
        }

        // Replace blank characters and convert ASCII chars
        $str = str_replace(" ", "", $str);
        $str = str_replace("\n", "", $str);
        $str = $this->replaceAsciiChars($str);

        // Write string
        $this->interpreterPtr->stdout->writeString($str);

    }


    /**
     * Execude READ
     */
    private function execute_read() : void
    {

        // Check if second argument is type, then check if type is int and value is also int
        if($this->arg2->getType() != "type") {
            throw new InvalidSourceStructureException("The second argument of READ must be type.");
        }

        if($this->arg1->getType() == "var") {

            $read = $this->interpreterPtr->input->readString();

            // Read from frame
            $this->interpreterPtr->frames["GF"]->setVariable(
                $this->arg1->getSecondValue(), 
                $read,
                $this->arg1->getType()
            );   

        }

    }


    /**
     * Execude READ
     */
    private function execute_defvar() : void
    {

        // Choose frame
        if($this->arg1->getFirstValue() == "GF") {
            $this->interpreterPtr->frames["GF"]->defineVariable($this->arg1->getSecondValue());
        }
        else if($this->arg1->getFirstValue() == "TF") {
            $this->interpreterPtr->frames["TF"]->defineVariable($this->arg1->getSecondValue());
        }
        else if($this->arg1->getFirstValue() == "LF") {
            $this->interpreterPtr->framesStack->peek()->defineVariable($this->arg1->getSecondValue());
        }
    
    }


    /**
     * Execude MOVE
     */
    private function execute_move() : void
    {

        $str = "";
        // If its not nil, save string
        if($this->arg2->getType() != "nil")
            $str = $this->arg2->getValue();

        // Choose frame
        if($this->arg1->getFirstValue() == "GF") {
            $this->interpreterPtr->frames["GF"]->setVariable(
                $this->arg1->getSecondValue(), 
                $str, $this->arg2->getType()
            );  
        }
        else if($this->arg1->getFirstValue() == "TF") {
            $this->interpreterPtr->frames["TF"]->setVariable(
                $this->arg1->getSecondValue(), 
                $str, $this->arg2->getType()
            );  
        }
        else if($this->arg1->getFirstValue() == "LF") {
            $this->interpreterPtr->framesStack->peek()->setVariable(
                $this->arg1->getSecondValue(),
                $str, $this->arg2->getType()
            );
        }
         

    }


    /**
     * Execude CREATEFRAME
     */
    private function execute_createframe() : void
    {

        $frame = new Frame();
        $this->interpreterPtr->frames["TF"] = $frame;
            
    }


    /**
     * Execude PUSHFRAME
     */
    private function execute_pushframe() : void
    {

        $this->interpreterPtr->framesStack->push($this->interpreterPtr->frames["TF"]);
        $this->execute_createframe();
            
    }


    /**
     * Execude POPFRAME
     */
    private function execute_popframe() : void
    {

        $this->interpreterPtr->frames["TF"] = $this->interpreterPtr->framesStack->pop();
            
    }


    /**
     *  Execude CONCAT
     */
    private function execute_concat() : void
    {

        $finalString = "";

        // Make final string
        if($this->arg2->getType() == "var") {
            if($this->arg2->getFirstValue() == "GF") {
                $finalString .= $this->interpreterPtr->frames["GF"]->getVariable($this->arg2->getSecondValue());
            }
            else if($this->arg2->getFirstValue() == "TF") {
                $finalString .= $this->interpreterPtr->frames["TF"]->getVariable($this->arg2->getSecondValue());
            }
            else if($this->arg2->getFirstValue() == "LF") {
                $finalString .= $this->interpreterPtr->framesStack->peek()->getVariable($this->arg2->getSecondValue());
            }
        }
        else {
            $finalString .= $this->arg2->getValue();
        }

        if($this->arg3->getType() == "var") {
            if($this->arg3->getFirstValue() == "GF") {
                $finalString .= $this->interpreterPtr->frames["GF"]->getVariable($this->arg3->getSecondValue());
            }
            else if($this->arg3->getFirstValue() == "TF") {
                $finalString .= $this->interpreterPtr->frames["TF"]->getVariable($this->arg3->getSecondValue());
            }
            else if($this->arg3->getFirstValue() == "LF") {
                $finalString .= $this->interpreterPtr->framesStack->peek()->getVariable($this->arg3->getSecondValue());
            }
        }
        else {
            $finalString .= $this->arg3->getValue();
        }


        // Choose frame
        if($this->arg1->getFirstValue() == "GF") {
            $this->interpreterPtr->frames["GF"]->setVariable(
                $this->arg1->getSecondValue(), 
                $finalString, $this->arg1->getType()
            );  
        }
        else if($this->arg1->getFirstValue() == "TF") {
            $this->interpreterPtr->frames["TF"]->setVariable(
                $this->arg1->getSecondValue(), 
                $finalString, $this->arg1->getType()
            );  
        }
        else if($this->arg1->getFirstValue() == "LF") {
            $this->interpreterPtr->framesStack->peek()->setVariable(
                $this->arg1->getSecondValue(),
                $finalString, $this->arg1->getType()
            );
        }
            
    }


    /**
     *  Execude LABEL
     */
    private function execute_label() : void
    {

        // Done in function foundInstructionByOrderNumber()
        
    }


    /**
     *  Execude JUMP
     */
    private function execute_jump() : void
    {

        if (!isset($this->interpreterPtr->labels[$this->arg1->getValue()]))
            throw new SemanticException("Label `".$this->arg1->getValue()."` do not exists.");
        else {
            $this->specialNextInstr = true;
            $this->specialNextInstrNum = $this->interpreterPtr->labels[$this->arg1->getValue()];
        }
        
    }


    /**
     *  Execude JUMPIFEQ
     */
    private function execute_jumpifeq() : void
    {

        // Check if their are the same type
        /*if($this->arg1->getType() != $this->arg1->getType())
            throw new SemanticException("JUMPIFEQ arguments must be the same type.");*/

        // Check if their are nil
        if($this->arg1->getType() == "nil" || $this->arg1->getType() == "nil")
            throw new SemanticException("`nil` can not be used in JUMPIFEQ operand.");

        // Jump if equal
        if(strcmp($this->arg2->getValue(), $this->arg3->getValue())) {
            if (!isset($this->interpreterPtr->labels[$this->arg1->getValue()]))
                throw new SemanticException("Label `".$this->arg1->getValue()."` do not exists.");
            else {
                $this->specialNextInstr = true;
                $this->specialNextInstrNum = $this->interpreterPtr->labels[$this->arg1->getValue()];
            }
        }

    }


    /**
     *  Execute CALL
     */
    private function execute_call() : void
    {
 
        if (!isset($this->interpreterPtr->labels[$this->arg1->getValue()]))
            throw new SemanticException("Label `".$this->arg1->getValue()."` do not exists.");
        else {

            // Push to stack
            $this->interpreterPtr->callStack->push($this->order + 1);

            // Set special next instruction
            $this->specialNextInstr = true;
            $this->specialNextInstrNum = $this->interpreterPtr->labels[$this->arg1->getValue()];

        }

    }


    /**
     *  Execute EXIT
     */
    private function execute_exit() : void
    {
        
        $returnCode = $this->arg1->getValue();

        if (is_numeric($returnCode) && $returnCode >= 0 && $returnCode <= 9) {
            throw new ExitProgramException("", $returnCode);
        }
        else {
            throw new OperandValueException("Return code in operand `EXIT` must be 0 to 9");
        }

    }


    /**
     *  Execute STRLEN
     */
    private function execute_strlen() : void
    {
        if($this->arg2->getType() == "int" || $this->arg2->getType() == "bool")
            throw new OperandValueException("Can not use `int` or `bool` in STRLEN");
        
        $lenght = strlen($this->arg2->getValue());

        // Choose frame
        if($this->arg1->getFirstValue() == "GF") {
            $this->interpreterPtr->frames["GF"]->setVariable(
                $this->arg1->getSecondValue(), 
                $lenght, "string"
            );  
        }
        else if($this->arg1->getFirstValue() == "TF") {
            $this->interpreterPtr->frames["TF"]->setVariable(
                $this->arg1->getSecondValue(), 
                $lenght, "string"
            );  
        }
        else if($this->arg1->getFirstValue() == "LF") {
            $this->interpreterPtr->framesStack->peek()->setVariable(
                $this->arg1->getSecondValue(),
                $lenght, "string"
            );
        }



    }
    

    /**
     *  Execute ADD
     */
    private function execute_add() : void
    {

        if($this->arg2->getDeepType() != "int" || $this->arg3->getDeepType() != "int") {
            throw new OperandTypeException("In ADD must be only integers, you have `".$this->arg2->getDeepType()."` and `".$this->arg3->getDeepType()."`");
        }

        $result = (int)$this->arg2->getValue() + (int)$this->arg3->getValue();

        // Save
        if($this->arg1->getFirstValue() == "GF") {
            $this->interpreterPtr->frames["GF"]->setVariable(
                $this->arg1->getSecondValue(), 
                $result, "int"
            );  
        }
        else if($this->arg1->getFirstValue() == "TF") {
            $this->interpreterPtr->frames["TF"]->setVariable(
                $this->arg1->getSecondValue(), 
                $result, "int"
            );  
        }
        else if($this->arg1->getFirstValue() == "LF") {
            $this->interpreterPtr->framesStack->peek()->setVariable(
                $this->arg1->getSecondValue(),
                $result, "int"
            );
        }        

    }
        

    /**
     *  Execute SUB
     */
    private function execute_sub() : void
    {

        if($this->arg2->getDeepType() != "int" || $this->arg3->getDeepType() != "int") {
            throw new OperandTypeException("In SUB must be only integers, you have `".$this->arg2->getDeepType()."` and `".$this->arg3->getDeepType()."`");
        }

        $result = (int)$this->arg3->getValue() - (int)$this->arg2->getValue();

        // Save
        if($this->arg1->getFirstValue() == "GF") {
            $this->interpreterPtr->frames["GF"]->setVariable(
                $this->arg1->getSecondValue(), 
                $result, "int"
            );  
        }
        else if($this->arg1->getFirstValue() == "TF") {
            $this->interpreterPtr->frames["TF"]->setVariable(
                $this->arg1->getSecondValue(), 
                $result, "int"
            );  
        }
        else if($this->arg1->getFirstValue() == "LF") {
            $this->interpreterPtr->framesStack->peek()->setVariable(
                $this->arg1->getSecondValue(),
                $result, "int"
            );
        }        

    }
            

    /**
     *  Execute MUL
     */
    private function execute_mul() : void
    {

        if($this->arg2->getDeepType() != "int" || $this->arg3->getDeepType() != "int") {
            throw new OperandTypeException("In MUL must be only integers, you have `".$this->arg2->getDeepType()."` and `".$this->arg3->getDeepType()."`");
        }

        $result = (int)$this->arg3->getValue() * (int)$this->arg2->getValue();

        // Save
        if($this->arg1->getFirstValue() == "GF") {
            $this->interpreterPtr->frames["GF"]->setVariable(
                $this->arg1->getSecondValue(), 
                $result, "int"
            );  
        }
        else if($this->arg1->getFirstValue() == "TF") {
            $this->interpreterPtr->frames["TF"]->setVariable(
                $this->arg1->getSecondValue(), 
                $result, "int"
            );  
        }
        else if($this->arg1->getFirstValue() == "LF") {
            $this->interpreterPtr->framesStack->peek()->setVariable(
                $this->arg1->getSecondValue(),
                $result, "int"
            );
        }        

    }
            

    /**
     *  Execute IDIV
     */
    private function execute_idiv() : void
    {

        if($this->arg2->getDeepType() != "int" || $this->arg3->getDeepType() != "int") {
            throw new OperandTypeException("In IDIV must be only integers, you have `".$this->arg2->getDeepType()."` and `".$this->arg3->getDeepType()."`");
        }

        if((int)$this->arg3->getValue() == 0) {
            throw new OperandValueException("Can not divide by zero.");
        }

        $result = (int)$this->arg2->getValue() / (int)$this->arg3->getValue();

        // Save
        if($this->arg1->getFirstValue() == "GF") {
            $this->interpreterPtr->frames["GF"]->setVariable(
                $this->arg1->getSecondValue(), 
                $result, "int"
            );  
        }
        else if($this->arg1->getFirstValue() == "TF") {
            $this->interpreterPtr->frames["TF"]->setVariable(
                $this->arg1->getSecondValue(), 
                $result, "int"
            );  
        }
        else if($this->arg1->getFirstValue() == "LF") {
            $this->interpreterPtr->framesStack->peek()->setVariable(
                $this->arg1->getSecondValue(),
                $result, "int"
            );
        }        

    }
                

    /**
     *  Execute LT
     */
    private function execute_lt() : void
    {

        $arg2DeepType = $this->arg2->getDeepType();

        if($arg2DeepType != $this->arg3->getDeepType() || $this->arg2->getDeepType() == "nil") {
            throw new OperandTypeException("In LT can not be `".$this->arg2->getDeepType()."` or `".$this->arg3->getDeepType()."`");
        }

        // Check which is result
        if($arg2DeepType == "string") {
            if(strlen($this->arg2->getValue()) < strlen($this->arg3->getValue())) {
                $result = "true";
            }
            else {
                $result = "false";
            }
        }
        else if($arg2DeepType == "bool") {
            if($this->arg2->getValue() == "false") {
                $result = "true";
            }
            else {
                $result = "false";
            }
        }
        else {
            if((int)$this->arg2->getValue() < (int)$this->arg3->getValue()) {
                $result = "true";
            }
            else {
                $result = "false";
            }
        }

        // Save
        if($this->arg1->getFirstValue() == "GF") {
            $this->interpreterPtr->frames["GF"]->setVariable(
                $this->arg1->getSecondValue(), 
                $result, "bool"
            );  
        }
        else if($this->arg1->getFirstValue() == "TF") {
            $this->interpreterPtr->frames["TF"]->setVariable(
                $this->arg1->getSecondValue(), 
                $result, "bool"
            );  
        }
        else if($this->arg1->getFirstValue() == "LF") {
            $this->interpreterPtr->framesStack->peek()->setVariable(
                $this->arg1->getSecondValue(),
                $result, "bool"
            );
        }        

    }
                

    /**
     *  Execute GT
     */
    private function execute_gt() : void
    {

        $arg2DeepType = $this->arg2->getDeepType();
        $arg3DeepType = $this->arg3->getDeepType();

        if($arg2DeepType != $arg3DeepType && $arg2DeepType != "nil" && $arg3DeepType != "nil") {
            throw new OperandTypeException("In LT can not be `".$this->arg2->getDeepType()."` or `".$this->arg3->getDeepType()."`");
        }

        // Check which is result
        if($arg2DeepType == "string") {
            if(strlen($this->arg2->getValue()) > strlen($this->arg3->getValue())) {
                $result = "true";
            }
            else {
                $result = "false";
            }
        }
        else if($arg2DeepType == "bool") {
            if($this->arg2->getValue() == "true") {
                $result = "true";
            }
            else {
                $result = "false";
            }
        }
        else {
            if((int)$this->arg2->getValue() > (int)$this->arg3->getValue()) {
                $result = "true";
            }
            else {
                $result = "false";
            }
        }

        // Save
        if($this->arg1->getFirstValue() == "GF") {
            $this->interpreterPtr->frames["GF"]->setVariable(
                $this->arg1->getSecondValue(), 
                $result, "bool"
            );  
        }
        else if($this->arg1->getFirstValue() == "TF") {
            $this->interpreterPtr->frames["TF"]->setVariable(
                $this->arg1->getSecondValue(), 
                $result, "bool"
            );  
        }
        else if($this->arg1->getFirstValue() == "LF") {
            $this->interpreterPtr->framesStack->peek()->setVariable(
                $this->arg1->getSecondValue(),
                $result, "bool"
            );
        }        

    }
                

    /**
     *  Execute EQ
     */
    private function execute_eq() : void
    {

        $arg2DeepType = $this->arg2->getDeepType();

        if($arg2DeepType != $this->arg3->getDeepType() || $this->arg2->getDeepType() == "nil") {
            throw new OperandTypeException("In LT can not be `".$this->arg2->getDeepType()."` or `".$this->arg3->getDeepType()."`");
        }

        // Check which is result
        if($arg2DeepType == "string") {
            if(strlen($this->arg2->getValue()) == strlen($this->arg3->getValue())) {
                $result = "true";
            }
            else {
                $result = "false";
            }
        }
        else if($arg2DeepType == "bool") {
            if($this->arg2->getValue() == $this->arg3->getValue()) {
                $result = "true";
            }
            else {
                $result = "false";
            }
        }
        else {
            if((int)$this->arg2->getValue() == (int)$this->arg3->getValue()) {
                $result = "true";
            }
            else {
                $result = "false";
            }
        }

        // Save
        if($this->arg1->getFirstValue() == "GF") {
            $this->interpreterPtr->frames["GF"]->setVariable(
                $this->arg1->getSecondValue(), 
                $result, "bool"
            );  
        }
        else if($this->arg1->getFirstValue() == "TF") {
            $this->interpreterPtr->frames["TF"]->setVariable(
                $this->arg1->getSecondValue(), 
                $result, "bool"
            );  
        }
        else if($this->arg1->getFirstValue() == "LF") {
            $this->interpreterPtr->framesStack->peek()->setVariable(
                $this->arg1->getSecondValue(),
                $result, "bool"
            );
        }        

    }


    /**
     *  Execute AND
     */
    private function execute_and() : void
    {

        $arg2DeepType = $this->arg2->getDeepType();
        $arg3DeepType = $this->arg3->getDeepType();

        if($arg2DeepType != "bool" || $arg3DeepType != "bool") {
            throw new OperandTypeException("In AND can not be `".$this->arg2->getDeepType()."` or `".$this->arg3->getDeepType()."`, can be only `bool`");
        }

        
        if($this->arg2->getValue() == "true" && $this->arg3->getValue() == "true") {
                $result = "true";
        }
        else {
            $result = "false";
        }
        

        // Save
        if($this->arg1->getFirstValue() == "GF") {
            $this->interpreterPtr->frames["GF"]->setVariable(
                $this->arg1->getSecondValue(), 
                $result, "bool"
            );  
        }
        else if($this->arg1->getFirstValue() == "TF") {
            $this->interpreterPtr->frames["TF"]->setVariable(
                $this->arg1->getSecondValue(), 
                $result, "bool"
            );  
        }
        else if($this->arg1->getFirstValue() == "LF") {
            $this->interpreterPtr->framesStack->peek()->setVariable(
                $this->arg1->getSecondValue(),
                $result, "bool"
            );
        }        

    }

    
    /**
     *  Execute OR
     */
    private function execute_or() : void
    {

        $arg2DeepType = $this->arg2->getDeepType();
        $arg3DeepType = $this->arg3->getDeepType();

        if($arg2DeepType != "bool" || $arg3DeepType != "bool") {
            throw new OperandTypeException("In OR can not be `".$this->arg2->getDeepType()."` or `".$this->arg3->getDeepType()."`, can be only `bool`");
        }

        
        if($this->arg2->getValue() == "true" || $this->arg3->getValue() == "true") {
                $result = "true";
        }
        else {
            $result = "false";
        }
        

        // Save
        if($this->arg1->getFirstValue() == "GF") {
            $this->interpreterPtr->frames["GF"]->setVariable(
                $this->arg1->getSecondValue(), 
                $result, "bool"
            );  
        }
        else if($this->arg1->getFirstValue() == "TF") {
            $this->interpreterPtr->frames["TF"]->setVariable(
                $this->arg1->getSecondValue(), 
                $result, "bool"
            );  
        }
        else if($this->arg1->getFirstValue() == "LF") {
            $this->interpreterPtr->framesStack->peek()->setVariable(
                $this->arg1->getSecondValue(),
                $result, "bool"
            );
        }        

    }

    
    /**
     *  Execute NOT
     */
    private function execute_not() : void
    {

        $arg2DeepType = $this->arg2->getDeepType();

        if($arg2DeepType != "bool") {
            throw new OperandTypeException("In NOT can not be `".$this->arg2->getDeepType()."`, can be only `bool`");
        }

        
        if($this->arg2->getValue() == "true") {
                $result = "false";
        }
        else {
            $result = "true";
        }
        

        // Save
        if($this->arg1->getFirstValue() == "GF") {
            $this->interpreterPtr->frames["GF"]->setVariable(
                $this->arg1->getSecondValue(), 
                $result, "bool"
            );  
        }
        else if($this->arg1->getFirstValue() == "TF") {
            $this->interpreterPtr->frames["TF"]->setVariable(
                $this->arg1->getSecondValue(), 
                $result, "bool"
            );  
        }
        else if($this->arg1->getFirstValue() == "LF") {
            $this->interpreterPtr->framesStack->peek()->setVariable(
                $this->arg1->getSecondValue(),
                $result, "bool"
            );
        }        

    }
}
