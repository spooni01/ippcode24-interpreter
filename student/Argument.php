<?php

namespace IPP\Student;

// External
use DOMDocument;
use IPP\Core\AbstractInterpreter;

// Internal
use IPP\Student\Exception\InvalidSourceStructureException; // return code 32
use IPP\Student\Instruction;


class Argument 
{
    
    private mixed $type;
    private mixed $value;
    private string $argPattern;
    private Instruction $instructionPtr;
    public string $tmpType; // Stores last type of found operand in while loop

    /*
     *  Constructor
     */
    public function __construct(string $value, string $type, string $argPattern, Instruction $instructionPtr)
    {

        // Clean $value
        $value = str_replace("\n", "", $value);
        $value = str_replace(" ", "", $value);

        // Setters
        $this->value = $value;
        $this->type = $type;
        $this->argPattern = $argPattern;
        $this->instructionPtr = $instructionPtr;

        // Check if type is equal expected type
        if(!$this->isTypeCorrect()) {
            throw new InvalidSourceStructureException("Argument must be `$argPattern`, in code is `$type`.");
        }

    }


    /**
     *  Get type
     */
    public function getType() : string
    {

        return $this->type;

    }


    /**
     *  Get deep type
     */
    public function getDeepType() : string
    {

        if($this->type == "var") {
            $tmpValue = $this->value;
            $tmpType = "";
            
            while(preg_match("/(GF|TF|LF)@(\\S+)/", $tmpValue, $matches)) {
                if($matches[1] == "GF") {
                    $tmpValue = $this->instructionPtr->interpreterPtr->frames["GF"]->getVariable(
                        $matches[2] 
                    );  
                    $tmpType = $this->instructionPtr->interpreterPtr->frames["GF"]->getType(
                        $matches[2] 
                    );                      

                }
                else if($matches[1] == "TF") {
                    $tmpValue = $this->instructionPtr->interpreterPtr->frames["TF"]->getVariable(
                        $matches[2] 
                    );  
                    $tmpType = $this->instructionPtr->interpreterPtr->frames["GF"]->getType(
                        $matches[2] 
                    );  
                }
                else if($matches[1] == "LF") {
                    $tmpValue = $this->instructionPtr->interpreterPtr->framesStack->peek()->getVariable(
                        $matches[2]
                    );
                    $tmpType = $this->instructionPtr->interpreterPtr->frames["GF"]->getType(
                        $matches[2] 
                    );  
                }
            }

            return $tmpType;
        }
        else
            return $this->type;

    }


    /**
     *  Get value
     */
    public function getValue() : mixed
    {

        if($this->type == "var") {
            $tmpValue = $this->value;
            
            while(preg_match("/(GF|TF|LF)@(\\S+)/", $tmpValue, $matches)) {
                if($matches[1] == "GF") {
                    $tmpValue = $this->instructionPtr->interpreterPtr->frames["GF"]->getVariable(
                        $matches[2] 
                    );  
                }
                else if($matches[1] == "TF") {
                    $tmpValue = $this->instructionPtr->interpreterPtr->frames["TF"]->getVariable(
                        $matches[2] 
                    );  
                }
                else if($matches[1] == "LF") {
                    $tmpValue = $this->instructionPtr->interpreterPtr->framesStack->peek()->getVariable(
                        $matches[2]
                    );
                }


                if($tmpValue == NULL)
                    $tmpValue = "x";
                if($this->isVar($tmpValue) == true) {
                    break;
                }
            }

            return $tmpValue;
        }
        else
            return $this->value;

    }

    
    /**
     *  Checks if type of argument are correct
     */
    private function isTypeCorrect() : bool
    {

        // var
        if($this->argPattern == "var") {
            if($this->type == "var")
                return true;
            else
                return false;
        }

        // symb
        else if($this->argPattern == "symb") {
            if (in_array($this->type, ["int", "bool", "string", "nil", "var"]))
                return true;
            else
                return false;
        }

        // label
        else if($this->argPattern == "label") {
            if($this->type == "label")
                return true;
            else
                return false;
        }

        // type
        else if($this->argPattern == "type") {
            if (in_array($this->type, ["int", "bool", "string", "nil", "label", "type", "var"]))
                return true;
            else
                return false;
        }

        else {
            return false;
        }

    }


    /**
     *  Get second value of var
     */
    public function getSecondValue() : string {

        $parts = explode('@', $this->value, 2);
        return $parts[1];

    }


    /**
     * Get first value of var
     */
    public function getFirstValue() : string {


        $parts = explode('@', $this->value, 2);
        return $parts[0];

    }

    
    /**
     *  Check if paramater is var
     */
    private function isVar(mixed $tmpValue) : bool
    {

        $parts = explode('@', $tmpValue, 2);
        if($parts[0] == "GF" || $parts[0] == "LF" || $parts[0] == "TF")
            return true;

        return false;

    }


}
