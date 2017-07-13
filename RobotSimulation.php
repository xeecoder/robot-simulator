<?php

namespace app;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL); 

class RobotSimulator {
     
  const MAX_X = 5;
  const MAX_Y = 5;
  const INPUT = 'input.txt';
    
  protected $positionX = 0;
  protected $positionY = 0;
  protected $positionF = 'NORTH';
  protected $placed = false;
   
  /**
   * Parse a single line from input file into a valid command and triggers respective action
   * @params string $command
   * @return boolean
   */
  protected function triggerAction($command) {
      $command = trim($command);
      $input = explode(' ', $command); 
      if (!$this->validateInput($input)) {
          return false;
      }
      switch($input[0]) {
           case 'PLACE':
              $this->place($input);
              break;
           case 'MOVE':
              $this->move($input);
              break;
           case 'LEFT':
           case 'RIGHT':
              $this->rotate($input);
              break;
           case 'REPORT':
              $this->report($input);
              break;
           default:
              echo "ignoring input - not a valid command: " . $input[0] . "\n\r";
              break;                    
      }
      return true;     
  }
  
   /**
   * Validates input paramters
   * @params array $input
   * @return boolean
   */
  protected function validateInput($input) {
      if (!is_array($input) || count($input) < 1) {
          echo "ignoring input - no command given\n\r";
          return false;
      }  
      return true;
  }
  
  /**
   * Check if the PLACE command has been issued already
   * @params void
   * @return boolean
   */
  protected function isPlaced() {
       if (!$this->placed) {
         echo "ignoring input - still expecting first PLACE\n\r";
         return false;
      }
      return true;
  }
  
  /**
   * Triggers the PLACE action
   * @params array $input
   * @return boolean
   */
  protected function place($input) {       
       $axis = explode(',', $input[1]);
       if ($axis[0] > self::MAX_X || $axis[1] > self::MAX_Y || $axis[0] < 0 || $axis[1] < 0) {
          echo "PLACE - avoid fall\n\r";
          return false;
       }
       $this->positionX = $axis[0];
       $this->positionY = $axis[1];
       $this->positionF = $axis[2];
       $this->placed = true;
       echo "PLACE - OK\r\n";
       return true;
  }
  
  /**
   * Rotates robot to 90 degree LEFT or RIGHT
   * @params array $input
   * @return boolean
   */
  protected function rotate($input) {
       if (!$this->isPlaced()) {
          return false; 
       } 
       if ($input[0] == 'LEFT') {
           switch ($this->positionF) {
              case 'NORTH':
                  $this->positionF = 'WEST';
                  break;
              case 'EAST':
                  $this->positionF = 'NORTH';
                  break;
              case 'SOUTH':
                  $this->positionF = 'EAST';
                  break;
              case 'WEST':
                  $this->positionF = 'SOUTH';
                  break;            
           } 
       } 
       if ($input[0] == 'RIGHT') {
           switch ($this->positionF) {
              case 'NORTH':
                  $this->positionF = 'EAST';
                  break;
              case 'EAST':
                  $this->positionF = 'SOUTH';
                  break;
              case 'SOUTH':
                  $this->positionF = 'WEST';
                  break;
              case 'WEST':
                  $this->positionF = 'NORTH';
                  break;            
           } 
       }  
       echo $input[0] . " - OK\n\r";     
  }
  
   /**
   * Moves robot to a unit in current direction
   * @params array $input
   * @return boolean
   */
  protected function move($input) {
      if (!$this->isPlaced()) {
          return false; 
      } 
      $moved = false;      
      switch ($this->positionF) {
          case 'NORTH':
              if ($this->positionY + 1 <= self::MAX_Y) {
                  $this->positionY++;
                  $moved = true;
              }
              break;
          case 'EAST':
              if ($this->positionX + 1 <= self::MAX_X) {
                  $this->positionX++;
                  $moved = true;
              }
              break;
          case 'SOUTH':
             if ($this->positionY - 1 >= 0) {
                  $this->positionY--;
                  $moved = true;
              }
              break;
          case 'WEST':
              if ($this->positionX - 1 >= 0) {
                  $this->positionX--;
                  $moved = true;
              }
              break;            
      } 
      if ($moved) {
          echo "MOVE - OK\n\r";          
      } else {
          echo "MOVE - avoid fall\n\r";
      }
      return true;
  }
  
  protected function report($input) {
      echo $this->positionX . ',' . $this->positionY . ',' . $this->positionF . "\n\r" ;
  }
  
  /**
   * Reads the input file line by line to process all commands
   * @params void
   * @return void
   */
  public function processInput() {
      $handle = @fopen(self::INPUT, "r");
      if ($handle) {
          while (($command = fgets($handle, 4096)) !== false) {
              $this->triggerAction($command);
          }
          if (!feof($handle)) {
              echo "Error: unexpected fgets() fail\n\r";
          }
          fclose($handle);
      }
  }    
}

$robotSimulator = new RobotSimulator();
$robotSimulator->processInput();

?>
