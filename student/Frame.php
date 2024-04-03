<?php

namespace IPP\Student;

class Frame 
{
    
    private array $frame = [];
    private Frames $currentFrame = Frames::Global; 
    private int $uniqueIds = 0; 

    /*
     *  Switch frames
     */
    public function setToGlobal()
    {
        $this->currentFrame = Frames::Global;
    }
    public function setToTemporaly()
    {
        $this->currentFrame = Frames::Temporaly;
    }
    public function setToLocal()
    {
        $this->currentFrame = Frames::Local;
    }

    /*
     *  Add variable into frame
     */
    public function addVariable(string $varName, string $varVal, VariablesTypes $varType)
    {
        // todo: kontrola typov ci su spravne value pri spravnom type
        $tmpVar = [
            "id" => 
            $this->uniqueIds,
            "name" => $varName,
            "frame" => $this->currentFrame,
            "value" => $varVal,
            "type" => $varType
        ];

        $this->uniqueIds++;
        array_push($this->frame, $tmpVar);
    }

    /*
     *  Delete all local variables
     */
    public function destroyLocalFrame()
    {

        $keepers = [];
        foreach ($this->frame as $var) {
            if ($var["frame"] !== Frames::Local) {
                $keepers[] = $var;
            }
        }
        $this->frame = $keepers;
        
    }

    /*
     *  Delete all temporaly variables
     */
    public function destroyTemporalyFrame()
    {

        $keepers = [];
        foreach ($this->frame as $var) {
            if ($var["frame"] !== Frames::Temporaly) {
                $keepers[] = $var;
            }
        }
        $this->frame = $keepers;
        
    }

    /*
     *  Print all variables into stdout
     */
    public function print(): void {
        
        echo "\n|------------------------------------------------------------------------|\n";
        printf("| %3s | %-20s | %5s | %-20s | %-10s |\n", "id","name","frame","value","type");
        echo "|------------------------------------------------------------------------|\n";
      
        // Loop through each variable in the frame
        foreach ($this->frame as $var) {
          $id = $var["id"];
          $name = $var["name"];
          $frameId = $var["frame"];
          $value = $var["value"];
          $type = $var["type"];
      
          printf("| %3d | %-20s | %5s | %-20s | %-10s |\n", $id, $name, $frameId->value, $value, $type->value);
        }

        echo "|------------------------------------------------------------------------|\n";

    }

}
