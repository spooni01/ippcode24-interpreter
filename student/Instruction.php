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
use IPP\Student\Exception\FrameAccessException; // return code 55
use IPP\Student\Exception\ValueException; // return code 56
use IPP\Student\Exception\OperandValueException; // return code 57
use IPP\Student\Exception\StringOperationException; // return code 58
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
        ["opCode" => "CLEARS", "params" => []],
        ["opCode" => "ADDS", "params" => []],
        ["opCode" => "SUBS", "params" => []],
        ["opCode" => "MULS", "params" => []],
        ["opCode" => "IDIVS", "params" => []],
        ["opCode" => "LTS", "params" => []],
        ["opCode" => "GTS", "params" => []],
        ["opCode" => "EQS", "params" => []],
        ["opCode" => "ANDS", "params" => []],
        ["opCode" => "ORS", "params" => []],
        ["opCode" => "NOTS", "params" => []],
        ["opCode" => "INT2CHARS", "params" => []],
        ["opCode" => "STRI2INTS", "params" => []],
        ["opCode" => "JUMPIFEQS", "params" => ["label"]],
        ["opCode" => "JUMPIFNEQS", "params" => ["label"]],
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
            if ($childNode->nodeType == XML_ELEMENT_NODE && $childNode instanceof DOMElement) {

                $cnt++;
                $tagName = $childNode->tagName;

                // Save to correct variable
                if($cnt == 1 && $numOfCorrectArguments >= 1) 
                    if ($tagName == "arg1")
                        $this->arg1 = new Argument($childNode->textContent, $childNode->getAttribute("type"), $opcodeParams[0], $this); 
                    else if  ($tagName == "arg2")
                        $this->arg2 = new Argument($childNode->textContent, $childNode->getAttribute("type"), $opcodeParams[1], $this); 
                    else if  ($tagName == "arg3")
                        $this->arg3 = new Argument($childNode->textContent, $childNode->getAttribute("type"), $opcodeParams[2], $this); 
                    else
                        throw new InvalidSourceStructureException("Invalid argument tag");
                else if($cnt == 2 && $numOfCorrectArguments >= 2)
                    if ($tagName == "arg1")
                        $this->arg1 = new Argument($childNode->textContent, $childNode->getAttribute("type"), $opcodeParams[0], $this); 
                    else if  ($tagName == "arg2")
                        $this->arg2 = new Argument($childNode->textContent, $childNode->getAttribute("type"), $opcodeParams[1], $this); 
                    else if  ($tagName == "arg3")
                        $this->arg3 = new Argument($childNode->textContent, $childNode->getAttribute("type"), $opcodeParams[2], $this); 
                    else
                        throw new InvalidSourceStructureException("Invalid argument tag");        
                else if($cnt == 3 && $numOfCorrectArguments >= 3) 
                    if ($tagName == "arg1")
                        $this->arg1 = new Argument($childNode->textContent, $childNode->getAttribute("type"), $opcodeParams[0], $this); 
                    else if  ($tagName == "arg2")
                        $this->arg2 = new Argument($childNode->textContent, $childNode->getAttribute("type"), $opcodeParams[1], $this); 
                    else if  ($tagName == "arg3")
                        $this->arg3 = new Argument($childNode->textContent, $childNode->getAttribute("type"), $opcodeParams[2], $this); 
                    else
                        throw new InvalidSourceStructureException("Invalid argument tag");
                else
                    throw new InvalidSourceStructureException("Maximum number of arguments for `$this->opcode` is $numOfCorrectArguments.");           
            
            }
        }

        if($cnt != $numOfCorrectArguments)
            throw new InvalidSourceStructureException("Invalid number of arguments.");

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
                $this->execute_return();
                break;
            case "PUSHS":
                $this->execute_pushs();
                break;
            case "POPS":
                $this->execute_pops();
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
                $this->execute_int2char();
                break;
            case "STRI2INT":
                $this->execute_str2int();
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
                $this->execute_getchar();
                break;
            case "SETCHAR":
                $this->execute_setchar();
                break;
            case "TYPE":
                $this->execute_type();
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
                $this->execute_jumpifneq();
                break;
            case "EXIT":
                $this->execute_exit();
                break;
            case "DPRINT":
                $this->execute_dprint();
                break;
            case "BREAK":
                $this->execute_break();
                break;
            case "ADDS":
                $this->execute_adds();
                break; 
            case "CLEARS":
                $this->execute_clears();
                break;  
            case "SUBS":
                $this->execute_subs();
                break;  
            case "MULS":
                $this->execute_muls();
                break;    
            case "IDIVS":
                $this->execute_idivs();
                break; 
            case "LTS":
                $this->execute_lts();
                break; 
            case "GTS":
                $this->execute_gts();
                break; 
            case "EQS":
                $this->execute_eqs();
                break; 
            case "ANDS":
                $this->execute_ands();
                break; 
            case "ORS":
                $this->execute_ors();
                break; 
            case "NOTS":
                $this->execute_nots();
                break; 
            case "INT2CHARS":
                $this->execute_int2chars();
                break; 
            case "STRI2INTS":
                $this->execute_stri2ints();
                break; 
            case "JUMPIFEQS":
                $this->execute_jumpifeqs();
                break; 
            case "JUMPIFNEQS":
                $this->execute_jumpifneqs();
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

        if($str == NULL) 
            return;

        // Replace blank characters and convert ASCII chars
        //$str = str_replace(" ", "", (string)$str);
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

            if($this->arg2->getValue() == "string")
                $read = $this->interpreterPtr->input->readString();
            else if($this->arg2->getValue() == "bool")
                $read = $this->interpreterPtr->input->readString();
            else
                $read = $this->interpreterPtr->input->readInt();

            // Save
            if($this->arg1->getFirstValue() == "GF") {
                $this->interpreterPtr->frames["GF"]->setVariable(
                    $this->arg1->getSecondValue(), 
                    $read,
                    $this->arg2->getValue()
                );   
            }
            else if($this->arg1->getFirstValue() == "TF") {
                if($this->interpreterPtr->frames["TF"] == NULL)
                    throw new FrameAccessException("Frame TF do not exists.");
    
                $this->interpreterPtr->frames["TF"]->setVariable(
                    $this->arg1->getSecondValue(), 
                    $read,
                    $this->arg2->getValue()
                );   
            }
            else if($this->arg1->getFirstValue() == "LF") {
                $this->interpreterPtr->framesStack->peek()->setVariable(
                    $this->arg1->getSecondValue(), 
                    $read,
                    $this->arg2->getValue()
                );   
            }

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
            if($this->interpreterPtr->frames["TF"] == NULL)
                throw new FrameAccessException("Frame TF do not exists.");

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

        $type = $this->arg2->getType();
        if($type == "var") {
            $type = $this->arg2->getDeepType();

        }

        // Choose frame
        if($this->arg1->getFirstValue() == "GF") {
            $this->interpreterPtr->frames["GF"]->setVariable(
                $this->arg1->getSecondValue(), 
                $str, $type
            );  
        }
        else if($this->arg1->getFirstValue() == "TF") {
            $this->interpreterPtr->frames["TF"]->setVariable(
                $this->arg1->getSecondValue(), 
                $str, $type
            );  
        }
        else if($this->arg1->getFirstValue() == "LF") {
            $this->interpreterPtr->framesStack->peek()->setVariable(
                $this->arg1->getSecondValue(),
                $str, $type
            );
        }
         

    }


    /**
     * Execude CREATEFRAME
     */
    private function execute_createframe() : void
    {

        $this->interpreterPtr->frames["TF"] = NULL;
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
        $arg2DeepType = $this->arg2->getDeepType();
        $arg3DeepType = $this->arg3->getDeepType();
        $value1 = $this->arg2->getValue();
        $value2 = $this->arg3->getValue();
        
        if($arg2DeepType == "var" && !preg_match("/(GF|TF|LF)@(\\S+)/", $value1, $matches)) {
            $arg2DeepType = "string";
        }

        if($arg2DeepType != $arg3DeepType ||  $arg2DeepType == "nil" || $arg3DeepType == "nil" || $arg2DeepType == "bool" || $arg3DeepType == "bool" ||  $arg2DeepType == "int" || $arg3DeepType == "int")
            throw new OperandTypeException("CONCAT must have same types, you have `".$arg2DeepType."` and `".$arg3DeepType."`.");

        $finalString = $value1 . $value2;

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

        $this->interpreterPtr->callStack->push($this->order + 1);
        
    }


    /**
     *  Execude JUMPIFEQ
     */
    private function execute_jumpifeq() : void
    {

        // Check if their are the same type
        if($this->arg2->getDeepType() != $this->arg3->getDeepType() && $this->arg2->getDeepType()  != "nil@nil" && $this->arg3->getDeepType() != "nil@nil")
            throw new OperandTypeException("JUMPIFEQ arguments must be the same type, you have `".$this->arg2->getDeepType()."` and `".$this->arg3->getDeepType()."`.");

        // Check if their are nil
        if($this->arg1->getType() == "nil" || $this->arg1->getType() == "nil")
            throw new SemanticException("`nil` can not be used in JUMPIFEQ operand.");

        // Jump if equal
        if($this->arg2->getValue() == $this->arg3->getValue()) {
            if (!isset($this->interpreterPtr->labels[$this->arg1->getValue()]))
                throw new SemanticException("Label `".$this->arg1->getValue()."` do not exists.");
            else {
                $this->specialNextInstr = true;
                $this->specialNextInstrNum = $this->interpreterPtr->labels[$this->arg1->getValue()];
            }
        }

    }


    /**
     *  Execude JUMPIFNEQ
     */
    private function execute_jumpifneq() : void
    {

        // Check if their are the same type
        if($this->arg2->getDeepType() != $this->arg3->getDeepType())
            throw new OperandTypeException("JUMPIFEQ arguments must be the same type.");

        // Check if their are nil
        if($this->arg1->getType() == "nil" || $this->arg1->getType() == "nil")
            throw new SemanticException("`nil` can not be used in JUMPIFEQ operand.");

        // Jump if not equal
        if($this->arg2->getValue() !== $this->arg3->getValue()) {
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
            $this->interpreterPtr->callStack->push($this->order);

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
        if($this->arg2->getDeepType() == "int" || $this->arg2->getDeepType() == "bool" || $this->arg2->getDeepType() == "nil")
            throw new OperandTypeException("Can not use `int` or `bool` in STRLEN");
        
        $lenght = strlen($this->arg2->getValue());

        // Choose frame
        if($this->arg1->getFirstValue() == "GF") {
            $this->interpreterPtr->frames["GF"]->setVariable(
                $this->arg1->getSecondValue(), 
                $lenght, "int"
            );  
        }
        else if($this->arg1->getFirstValue() == "TF") {
            $this->interpreterPtr->frames["TF"]->setVariable(
                $this->arg1->getSecondValue(), 
                $lenght, "int"
            );  
        }
        else if($this->arg1->getFirstValue() == "LF") {
            $this->interpreterPtr->framesStack->peek()->setVariable(
                $this->arg1->getSecondValue(),
                $lenght, "int"
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

        $result = (int)$this->arg2->getValue() - (int)$this->arg3->getValue();

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

        $result = abs(intdiv((int)$this->arg2->getValue(), (int)$this->arg3->getValue()));

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

        if($arg2DeepType != $this->arg3->getDeepType() && $this->arg2->getDeepType() != "nil" && $this->arg3->getDeepType() != "nil") {
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


    /**
     *  Execute INT2CHAR
     */
    private function execute_int2char() : void
    {

        $arg2DeepType = $this->arg2->getDeepType();
        $num = $this->arg2->getValue();

        if($arg2DeepType != "int" && $num >= 0) {
            throw new OperandTypeException("In INT2CHAR can not be `".$this->arg2->getDeepType()."`, can be only `int`");
        }

        // Transfer
        $num = mb_chr($num);
        if(empty($num))
            throw new StringOperationException("In INT2CHAR is unicode, that do not represent any char.");


        // Save
        if($this->arg1->getFirstValue() == "GF") {
            $this->interpreterPtr->frames["GF"]->setVariable(
                $this->arg1->getSecondValue(), 
                $num, "bool"
            );  
        }
        else if($this->arg1->getFirstValue() == "TF") {
            $this->interpreterPtr->frames["TF"]->setVariable(
                $this->arg1->getSecondValue(), 
                $num, "bool"
            );  
        }
        else if($this->arg1->getFirstValue() == "LF") {
            $this->interpreterPtr->framesStack->peek()->setVariable(
                $this->arg1->getSecondValue(),
                $num, "bool"
            );
        }  

    }


    /**
     *  Execute STR2INT
     */
    private function execute_str2int() : void
    {

        $arg2DeepType = $this->arg2->getDeepType();
        $arg3DeepType = $this->arg2->getDeepType();
        $value = $this->arg2->getValue();
        $pos = $this->arg3->getValue();


        if($arg2DeepType != "int" && $arg3DeepType != "int") {
            throw new OperandTypeException("In STR2INT can not be `".$this->arg2->getDeepType()."`, can be only `string`");
        }

        // Transfer
        $value = mb_substr($value, (int)$pos, 1, 'UTF-8');

        if(empty($value))
            throw new StringOperationException("In STR2INT is char that do not represent any UNICODE integer.");


        // Save
        if($this->arg1->getFirstValue() == "GF") {
            $this->interpreterPtr->frames["GF"]->setVariable(
                $this->arg1->getSecondValue(), 
                $value, "bool"
            );  
        }
        else if($this->arg1->getFirstValue() == "TF") {
            $this->interpreterPtr->frames["TF"]->setVariable(
                $this->arg1->getSecondValue(), 
                $value, "bool"
            );  
        }
        else if($this->arg1->getFirstValue() == "LF") {
            $this->interpreterPtr->framesStack->peek()->setVariable(
                $this->arg1->getSecondValue(),
                $value, "bool"
            );
        }  

    }


    /**
     *  Execute GETCHAR
     */
    private function execute_getchar() : void
    {

        $arg2DeepType = $this->arg2->getDeepType();
        $arg3DeepType = $this->arg3->getDeepType();
        $value = $this->arg2->getValue();
        $pos = $this->arg3->getValue();


        if($arg2DeepType != "string" || $arg3DeepType != "int") {
            throw new OperandTypeException("In GETCHAR can not be `".$this->arg2->getDeepType()."`, can be only `string`");
        }

        // Transfer
        $value = mb_substr($value, (int)$pos, 1, 'UTF-8');

        if(empty($value))
            throw new OperandTypeException("In STR2INT is char that do not represent any UNICODE integer.");


        // Save
        if($this->arg1->getFirstValue() == "GF") {
            $this->interpreterPtr->frames["GF"]->setVariable(
                $this->arg1->getSecondValue(), 
                $value, "string"
            );  
        }
        else if($this->arg1->getFirstValue() == "TF") {
            $this->interpreterPtr->frames["TF"]->setVariable(
                $this->arg1->getSecondValue(), 
                $value, "string"
            );  
        }
        else if($this->arg1->getFirstValue() == "LF") {
            $this->interpreterPtr->framesStack->peek()->setVariable(
                $this->arg1->getSecondValue(),
                $value, "string"
            );
        }    

    }


    /**
     *  Execute SETCHAR
     */
    private function execute_setchar() : void
    {

        $arg2DeepType = $this->arg2->getDeepType();
        $arg3DeepType = $this->arg3->getDeepType();
        $newChar = $this->arg3->getValue();
        $pos = $this->arg2->getValue();
        $oldStr = $this->arg1->getValue();

        if($arg2DeepType == "nil" || $arg3DeepType == "nil" || $arg2DeepType == "bool" || $arg3DeepType == "bool" || $arg3DeepType == "int" || $arg2DeepType == "string") {
            throw new OperandTypeException("In GETCHAR can not be `".$this->arg2->getDeepType()."`, can be only `string`");
        }

        $len = strlen($oldStr);
        if ($pos < 0 || $pos >= $len || $newChar == "" || $newChar == NULL) {
            throw new StringOperationException("Error in SETCHAR operand");
        }
        $value = substr_replace($oldStr, $newChar, $pos, 1);

        // Save
        if($this->arg1->getFirstValue() == "GF") {
            $this->interpreterPtr->frames["GF"]->setVariable(
                $this->arg1->getSecondValue(), 
                $value, "string"
            );  
        }
        else if($this->arg1->getFirstValue() == "TF") {
            $this->interpreterPtr->frames["TF"]->setVariable(
                $this->arg1->getSecondValue(), 
                $value, "string"
            );  
        }
        else if($this->arg1->getFirstValue() == "LF") {
            $this->interpreterPtr->framesStack->peek()->setVariable(
                $this->arg1->getSecondValue(),
                $value, "string"
            );
        }    

    }


    /**
     *  Execute TYPE
     */
    private function execute_type() : void
    {

        $type = $this->arg2->getDeepType();

        if($type == "nil@nil")
            return;

        // Save
        if($this->arg1->getFirstValue() == "GF") {
            $this->interpreterPtr->frames["GF"]->setVariable(
                $this->arg1->getSecondValue(), 
                $type, "string"
            );  
        }
        else if($this->arg1->getFirstValue() == "TF") {
            $this->interpreterPtr->frames["TF"]->setVariable(
                $this->arg1->getSecondValue(), 
                $type, "string"
            );  
        }
        else if($this->arg1->getFirstValue() == "LF") {
            $this->interpreterPtr->framesStack->peek()->setVariable(
                $this->arg1->getSecondValue(),
                $type, "string"
            );
        }  


    }


    /**
     *  Execute DPRINT 
     */
    private function execute_dprint() : void
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
        $str = str_replace(" ", "", (string)$str);
        $str = str_replace("\n", "", $str);
        $str = $this->replaceAsciiChars($str);

        // Write string
        $this->interpreterPtr->stderr->writeString($str);

    }


    /**
     *  Execute BREAK 
     */
    private function execute_break() : void
    {

        $this->interpreterPtr->stderr->writeString("\nPosition in code: ".$this->order."\nGlobal frame values: ");
        $this->interpreterPtr->stderr->writeString($this->interpreterPtr->frames["GF"]->flush()."");
        $this->interpreterPtr->stderr->writeString("\nTemporaly frame values: ");
        $this->interpreterPtr->stderr->writeString($this->interpreterPtr->frames["TF"]->flush()."\n\n");

    }


    /**
     *  Execute RETURN
     */
    private function execute_return() : void
    {

        if ($this->interpreterPtr->callStack->size() == 0)
            throw new ValueException("No place to jump.");
        else {
            $jumpPos = $this->interpreterPtr->callStack->pop();

            $this->specialNextInstr = true;
            $this->specialNextInstrNum = $jumpPos;
        }

    }


    /**
     *  Execute PUSHS
     */
    private function execute_pushs() : void
    {

        $this->interpreterPtr->dataStack->push($this->arg1->getValue());
        $this->interpreterPtr->dataStackTypes->push($this->arg1->getDeepType());

    }


    /**
     *  Execute POPS
     */
    private function execute_pops() : void
    {

        if ($this->interpreterPtr->dataStack->size() == 0)
            throw new ValueException("No data to pop.");

        $data = $this->interpreterPtr->dataStack->pop();
        $dataType = $this->interpreterPtr->dataStackTypes->pop();
       
        if($dataType == "var" && !preg_match("/(GF|TF|LF)@(\\S+)/", $data, $matches)) {
            $dataType = "string";
        }


        // Save
        if($this->arg1->getFirstValue() == "GF") {
            
            $this->interpreterPtr->frames["GF"]->setVariable(
                $this->arg1->getSecondValue(), 
                $data, $dataType
            );  
        }
        else if($this->arg1->getFirstValue() == "TF") {
            $this->interpreterPtr->frames["TF"]->setVariable(
                $this->arg1->getSecondValue(), 
                $data, $dataType
            );  
        }
        else if($this->arg1->getFirstValue() == "LF") {
            $this->interpreterPtr->framesStack->peek()->setVariable(
                $this->arg1->getSecondValue(),
                $data, $dataType
            );
        }    

    }


    /**
     *  Execute ADDS
     */
    private function execute_adds() : void {

        $val1 = $this->interpreterPtr->dataStack->pop();
        $val2 = $this->interpreterPtr->dataStack->pop();

        $type1 = $this->interpreterPtr->dataStackTypes->pop();
        $type2 = $this->interpreterPtr->dataStackTypes->pop();

        if($type1 != "int" || $type2 != "int")
            throw new OperandTypeException("Operands in ADDS must be int");

        $final = $val1+$val2;

        $this->interpreterPtr->dataStack->push((string)$final);
        $this->interpreterPtr->dataStackTypes->push((string)"int");

    }


    /**
     *  Execute CLEARS
     */
    private function execute_clears() : void {

        $this->interpreterPtr->dataStack->clear();
        $this->interpreterPtr->dataStackTypes->clear();

    }


    /**
     *  Execute SUBS
     */
    private function execute_subs() : void {

        $val1 = $this->interpreterPtr->dataStack->pop();
        $val2 = $this->interpreterPtr->dataStack->pop();

        $type1 = $this->interpreterPtr->dataStackTypes->pop();
        $type2 = $this->interpreterPtr->dataStackTypes->pop();

        if($type1 != "int" || $type2 != "int")
            throw new OperandTypeException("Operands in SUBS must be int");

        $final = $val2-$val1;

        $this->interpreterPtr->dataStack->push((string)$final);
        $this->interpreterPtr->dataStackTypes->push((string)"int");

    }


    /**
     *  Execute MULS
     */
    private function execute_muls() : void {

        $val1 = $this->interpreterPtr->dataStack->pop();
        $val2 = $this->interpreterPtr->dataStack->pop();

        $type1 = $this->interpreterPtr->dataStackTypes->pop();
        $type2 = $this->interpreterPtr->dataStackTypes->pop();

        if($type1 != "int" || $type2 != "int")
            throw new OperandTypeException("Operands in MULS must be int");

        $final = $val2*$val1;

        $this->interpreterPtr->dataStack->push((string)$final);
        $this->interpreterPtr->dataStackTypes->push((string)"int");

    }


    /**
     *  Execute IDIVS
     */
    private function execute_idivs() : void {

        $val1 = $this->interpreterPtr->dataStack->pop();
        $val2 = $this->interpreterPtr->dataStack->pop();

        $type1 = $this->interpreterPtr->dataStackTypes->pop();
        $type2 = $this->interpreterPtr->dataStackTypes->pop();

        if($type1 != "int" || $type2 != "int")
            throw new OperandTypeException("Operands in IDIVSS must be int");

        if((int)$val1 == 0) {
            throw new OperandValueException("Can not divide by zero.");
        }
    
        $final = intdiv($val2, $val1);

        $this->interpreterPtr->dataStack->push((string)$final);
        $this->interpreterPtr->dataStackTypes->push((string)"int");

    }


    /**
     *  Execute LTS
     */
    private function execute_lts() : void {

        $val1 = $this->interpreterPtr->dataStack->pop();
        $val2 = $this->interpreterPtr->dataStack->pop();

        $type1 = $this->interpreterPtr->dataStackTypes->pop();
        $type2 = $this->interpreterPtr->dataStackTypes->pop();

        if($type1 != $type2 || $type1 == "nil" || $type2 == "nil")
            throw new OperandTypeException("Operands in EQS must be same type");

        if($val2 < $val1)
            $final = "true";
        else
            $final = "false";

        $this->interpreterPtr->dataStack->push((string)$final);
        $this->interpreterPtr->dataStackTypes->push((string)"bool");

    }


    /**
     *  Execute GTS
     */
    private function execute_gts() : void {

        $val1 = $this->interpreterPtr->dataStack->pop();
        $val2 = $this->interpreterPtr->dataStack->pop();

        $type1 = $this->interpreterPtr->dataStackTypes->pop();
        $type2 = $this->interpreterPtr->dataStackTypes->pop();

        if($type1 != $type2 || $type1 == "nil" || $type2 == "nil")
            throw new OperandTypeException("Operands in EQS must be same type");

        if($val2 > $val1)
            $final = "true";
        else
            $final = "false";

        $this->interpreterPtr->dataStack->push((string)$final);
        $this->interpreterPtr->dataStackTypes->push((string)"bool");

    }


    /**
     *  Execute EQS
     */
    private function execute_eqs() : void {

        $val1 = $this->interpreterPtr->dataStack->pop();
        $val2 = $this->interpreterPtr->dataStack->pop();

        $type1 = $this->interpreterPtr->dataStackTypes->pop();
        $type2 = $this->interpreterPtr->dataStackTypes->pop();

        if($type1 != $type2 && $type1 != "nil" && $type2 != "nil")
            throw new OperandTypeException("Operands in EQS must be same type");

        if($val2 == $val1)
            $final = "true";
        else
            $final = "false";

        $this->interpreterPtr->dataStack->push((string)$final);
        $this->interpreterPtr->dataStackTypes->push((string)"bool");

    }


    /**
     *  Execute ANDS
     */
    private function execute_ands() : void {

        $val1 = $this->interpreterPtr->dataStack->pop();
        $val2 = $this->interpreterPtr->dataStack->pop();

        $type1 = $this->interpreterPtr->dataStackTypes->pop();
        $type2 = $this->interpreterPtr->dataStackTypes->pop();

        if($type1 != "bool" || $type2 != "bool")
            throw new OperandTypeException("Operands in ANDS must be same type");

        if($val2 == "true" && $val1 == "true")
            $final = "true";
        else
            $final = "false";

        $this->interpreterPtr->dataStack->push((string)$final);
        $this->interpreterPtr->dataStackTypes->push((string)"bool");

    }


    /**
     *  Execute ORS
     */
    private function execute_ors() : void {

        $val1 = $this->interpreterPtr->dataStack->pop();
        $val2 = $this->interpreterPtr->dataStack->pop();

        $type1 = $this->interpreterPtr->dataStackTypes->pop();
        $type2 = $this->interpreterPtr->dataStackTypes->pop();

        if($type1 != "bool" || $type2 != "bool")
            throw new OperandTypeException("Operands in ANDS must be boolean");

        if($val2 == "true" || $val1 == "true")
            $final = "true";
        else
            $final = "false";

        $this->interpreterPtr->dataStack->push((string)$final);
        $this->interpreterPtr->dataStackTypes->push((string)"bool");

    }


    /**
     *  Execute NOTS
     */
    private function execute_nots() : void {

        $val1 = $this->interpreterPtr->dataStack->pop();
        $type1 = $this->interpreterPtr->dataStackTypes->pop();

        if($type1 != "bool")
            throw new OperandTypeException("Operands in NOTS must be boolean");

        if($val1 == "false")
            $final = "true";
        else
            $final = "false";

        $this->interpreterPtr->dataStack->push((string)$final);
        $this->interpreterPtr->dataStackTypes->push((string)"bool");

    }


    /**
     *  Execute INT2CHARS
     */
    private function execute_int2chars() : void {

        $val1 = $this->interpreterPtr->dataStack->pop();
        $type1 = $this->interpreterPtr->dataStackTypes->pop();

        if($type1 != "int" && $val1 >= 0) {
            throw new OperandTypeException("In INT2CHARS can not be `".$type1."`, can be only `int`");
        }

        // Transfer
        $final = mb_chr($val1);
        if(empty($final))
            throw new StringOperationException("In INT2CHARS is unicode, that do not represent any char.");


        // Save
        $this->interpreterPtr->dataStack->push((string)$final);
        $this->interpreterPtr->dataStackTypes->push((string)"string");

    }


    /**
     *  Execute STRI2INTS
     */
    private function execute_stri2ints() : void {

        $val1 = $this->interpreterPtr->dataStack->pop();
        $type1 = $this->interpreterPtr->dataStackTypes->pop();
        $val2 = $this->interpreterPtr->dataStack->pop();
        $type2 = $this->interpreterPtr->dataStackTypes->pop();

        if($type1 != "int" && $type2 != "int") {
            throw new OperandTypeException("In STR2INT can not be `".$type1."`, can be only `string`");
        }

        // Transfer
        $val1 = mb_substr($val2, (int)$val1, 1, 'UTF-8');

        if(empty($val1))
            throw new StringOperationException("In STR2INT is char that do not represent any UNICODE integer.");

        // Save
        $this->interpreterPtr->dataStack->push((string)$val1);
        $this->interpreterPtr->dataStackTypes->push((string)"int");
        

    }


    /**
     *  Execute JUMPIFEQS
     */
    private function execute_jumpifeqs() : void {

        $val1 = $this->interpreterPtr->dataStack->pop();
        $type1 = $this->interpreterPtr->dataStackTypes->pop();
        $val2 = $this->interpreterPtr->dataStack->pop();
        $type2 = $this->interpreterPtr->dataStackTypes->pop();

        // Check if their are the same type
        if($type1 != $type2 && $type1 != "nil" && $type2 != "nil")
            throw new OperandTypeException("JUMPIFEQS arguments must be the same type, you have `".$type1."` and `".$type2."`.");

        // Jump if equal
        if($val1 == $val2) {
            if (!isset($this->interpreterPtr->labels[$this->arg1->getValue()]))
                throw new SemanticException("Label `".$this->arg1->getValue()."` do not exists.");
            else {
                $this->specialNextInstr = true;
                $this->specialNextInstrNum = $this->interpreterPtr->labels[$this->arg1->getValue()];
            }
        }

    }


    /**
     *  Execute JUMPIFNEQS
     */
    private function execute_jumpifneqs() : void {

        $val1 = $this->interpreterPtr->dataStack->pop();
        $type1 = $this->interpreterPtr->dataStackTypes->pop();
        $val2 = $this->interpreterPtr->dataStack->pop();
        $type2 = $this->interpreterPtr->dataStackTypes->pop();

        // Check if their are the same type
        if($type1 != $type2 && $type1 != "nil" && $type2 != "nil")
            throw new OperandTypeException("JUMPIFEQS arguments must be the same type.");

        // Jump if equal
        if($val1 != $val2) {
            if (!isset($this->interpreterPtr->labels[$this->arg1->getValue()]))
                throw new SemanticException("Label `".$this->arg1->getValue()."` do not exists.");
            else {
                $this->specialNextInstr = true;
                $this->specialNextInstrNum = $this->interpreterPtr->labels[$this->arg1->getValue()];
            }
        }

    }


}
