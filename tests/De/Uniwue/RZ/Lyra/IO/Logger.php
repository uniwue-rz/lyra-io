<?php
/**
* A simple logger for the test
*
*/

namespace De\Uniwue\RZ\Lyra\IO;

class Logger{

    /**
    * simple logger with log method that used in test
    *
    * @param string $level      The level of log that should be used
    * @param string $message    The message that should be printed
    **/
    public function log($level, $message){

        echo "[$level] with message: $message\n";
    }
}