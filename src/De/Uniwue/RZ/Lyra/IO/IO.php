<?php
/**
* The IO library of Lyra application. It wraps the filesystem component of Symfony and also adds
* more flexibility and functionality to the component.
*
* @author Pouyan Azari <pouyan.azari@uni-wuerzburg.de>
* @license MIT
*/

namespace De\Uniwue\RZ\Lyra\IO;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

class IO{

    /**
    * Placeholder the logger object
    *
    * @var Logger
    */
    private $logger;

    /**
    * Placeholder for the filesystem
    * @var Filesystem
    */
    private $fs;

    /**
    * Constructor
    *
    * @param Container $container   The service container
    * @param string    $loggerName  The logger object service name
    *                               It should support the function log with options $level and $message at least
    */
    public function __construct($logger = null){
        $this->logger = $logger;
        $this->fs = new Filesystem();
    }

    /**
    * Returns the filesystem handler for the application
    *
    * @return FileSystem
    **/
    public function getFS(){
        
        return $this->fs;
    }

    /**
    * Sets the filesystem handler for the IO
    *
    * @param FileSystem $fs The filesystem handler for the IO
    */
    public function setFs($fs){
        $this->fs = $fs;
    }

    /**
    * This can be used to log to the given application. It only works container and logger name are set.
    *
    * @param string $level The log level for the logging in lowercase
    * @param string $message The message to the logger
    * @param string $context The context of log
    */
    public function log($level, $message, $context = array()){
        if($this->logger !== null){
            $this->logger->log($level, $message, $context);
        }
    }

    /**
    * Creates a directory with the given mode and path.
    *
    * @param mix $path The path which can be a string or an array or anything traversable
    * @param int $mode The file mode that should be used
    * @param bool $dryrun The dryrun for the given query
    *
    * @throws Exception
    */
    public function mkdir($path, $mode=0777, $dryrun = false){
        if($dryrun === true){
            // The base convert should be done
            $this->log("info", "DRYRUN: The " .(string)$path ." is created with mode ". $this->normalizeMode($mode));

            return;
        }
        $this->fs->mkdir($path, $mode);
        $this->log("info", "The " .(string)$path ." is created with mode ". $this->normalizeMode($mode));
    }

    /**
    * Removes the given director(ies) or file(s)
    *
    * @param mix     $paths   The paths to the directories or file(s) that should be deleted
    * @param bool    $dryrun  The dryrun flag for the command
    */
    public function remove($paths, $dryrun = false){
        if($dryrun === true){
            
            $this->log("info","DRYRUN: The ". $this->pathsString($paths)." deleted");

            return;
        }
        $this->fs->remove($paths);
        $this->log("info", "The ". $this->pathsString($paths) ." deleted");
    }

    /**
    * Converts the paths to string
    *
    * @param string|traversable $paths The paths that should be converted to string
    *
    * @return string
    **/
    public function pathsString($paths){
        if(!$paths instanceof \Traversable ){

            return $paths;
        }
        return \implode("\n", $paths);
    }

    /**
    * Checks if the given
    *
    * @param mix  $paths The path or paths that should exists, returns false when one of them not found
    */
    public function exists($paths){
        return $this->fs->exists($paths);
    }

    /**
    * Can be used to copy a file, or directory (recursively) in the given destination.
    * This does not have any recursion limit, but PHP will normally break up after a certain recursion level
    *
    * @param string $source         The source of the copying
    * @param string $destination    The destination of the copying
    * @param bool   $override       If the destination files/directories should be overridden
    * @param bool   $dryrun         The dryrun flag for the given action
    **/
    public function copy($source, $destination, $mode=0777, $override=false, $dryrun = false){
        // Check if the given source is a directory
        if(\is_dir($source)){
            $dir = \opendir($source);
            if($this->exists($destination) === false){
                // Create destination directory if not exists
                $this->mkdir($destination, $mode, $dryrun);
                // go through the list of files and dirs in the given directory
                while($file = \readdir($dir)){
                    if($this->fileNameIsValid($file) === true){
                        // Create the source path and destination path
                        $sourcePath = $source."/".$file;
                        $destinationPath = $destination."/".$file;
                        // Use the copy to copy the files
                        $this->copy($sourcePath, $destinationPath, $mode, $override, $dryrun);
                    }
                }
            }
            \closedir($dir);
        } else{
            if($dryrun === true){
                $this->log("info", "DRYRUN: Copied $source to $destination");

                return;
            }
            $this->fs->copy($source, $destination, $override);
            $this->log("info", "Copied $source to $destination");
        }
    }

    /**
    * Touches the given directory or file
    *
    * @param string $path       The path to the given file/dir
    * @param int    $modified   The modification time
    * @param int    $access     The access time
    * @param bool   $dryrun     The dryrun flag for the operation
    */
    public function touch($path, $modified = null, $access = null, $dryrun = false){
        
        if($modified === null){
            $modified = \time();
        }
        if($access === null){
            $access =  \time();
        }
        if($dryrun === true){
            $this->log("info", "DRYRUN: Touched the file $path with modification time $modified and access: $access");
            return ;
        }
        $this->fs->touch($path, $modified, $access);
        $this->log("info", "Touched the file $path with modification time $modified and access: $access");
    }

    /**
    * Check if the given file name is valid
    *
    * @param string $fileName The name of the given file
    *
    * @return bool
    */
    public function fileNameIsValid($fileName){
        if($fileName ==="." || $fileName === ".."){

            return false;
        }

        return true;
    }

    /**
    * Normalizes the mode the a valid written string
    * form https://stackoverflow.com/a/3240479
    *
    * @param int $mode The mode integer
    *
    * @return string
    */
    public function normalizeMode($mode){

        return base_convert((string) $mode, 10, 8);
    }

    /**
    * Change the owner and group of the given file or directory. Can apply recursively
    *
    * @param string|array|traversable $path       The path to the given file or directory
    * @param string                   $owner      The new owner of the given file or folder
    * @param string                   $group      The group that should own something
    * @param bool                     $recursive  If the owner should be changed recursively
    * @param bool                     $dryrun     The dryrun flag for the given system
    */
    public function chown($paths, $owner, $group =null, $recursive = false, $dryrun = false){
        if(dryrun === true){
            $this->log("info", "DRYRUN: Changed the owner of " . $this->pathsString($paths) . "to $owner and $group with recursive flag: $recursive");

            return;
        }
        $this->fs->chown($paths, $owner, $recursive);
        if($group !== null){
            $this->fs->chgrp($paths, $group, $recursive);
        }
        $this->log("info", "Changed the owner of " . $this->pathsString($paths) . "to $owner and $group with recursive flag: $recursive");
    }

    /**
    * Changes the file or directory permission. Can be used in recursive way
    *
    * @param string|array|traversable $paths        The path(s) to the given directory or file
    * @param int                      $mode         The new mode of the given file or directory
    * @param int                      $umask        The new umask of the given directory
    * @param bool                     $recursive    The recursive flag for the operation
    * @param bool                     $dryrun       The dryrun flag for the operation
    */
    public function chmod($paths, $mode, $umask = 0000, $recursive = false, $dryrun = false){
        if($dryrun === true){
            $this->log("info", "DRYRUN: Change the permission of ". 
                        $this->pathsString($paths). "to mode: ". 
                        $this->normalizeMode($mode) . " and umask: ".
                        $this->normalizeMode($umask). "with recursive flag: $recursive"
            );

            return ;
        }
        $this->fs->chmod($paths, $mode, $umask, $recursive);
        $this->log("info", "Change the permission of ". 
                    $this->pathsString($paths). "to mode: ". 
                    $this->normalizeMode($mode) . " and umask: ".
                    $this->normalizeMode($umask). "with recursive flag: $recursive"
        );
    }

    /**
    * This is the same method as rename. For backward compatibility
    *
    * @param string $src            The source of the move
    * @param string $destination    Destination of the move
    * @param bool   $override       If the file should be rewritten
    * @param bool   $dryrun         The dryrun flag for the given query
    */
    public function rename($src, $destination, $override = false, $dryrun = false){
        if($dryrun === true){
            $this->log("info", "DRYRUN: $src is renamed to $destination with override flag: $override");

            return;
        }
        $this->fs->rename($src, $destination, $override);
        $this->log("info", "$src is renamed to $destination with override flag: $override");
    }

    /**
    * This is the same method as rename. For backward compatibility
    *
    * @param string $src            The source of the move
    * @param string $destination    Destination of the move
    * @param bool   $override       If the file should be rewritten
    * @param bool   $dryrun         The dryrun flag for the given query
    */
    public function move($src, $destination, $override = false, $dryrun = false){
        return $this->rename($src, $destination, $override, $dryrun);
    }

    /**
    * Dumps the given data to the given file.
    *
    * @param string $path   The path to the file that should be written
    * @param string $content The content of the file to be written
    * @param bool   $dryrun  The dryrun flag for the operation
    */
    public function dumpFile($path, $content, $dryrun = false){
        if($dryrun === true){
            $this->log("info", "DRYRUN: Write the content to $path");

            return;
        }
        $this->fs->dumpFile($path, $content);
        $this->log("info", "Write the content to $path");
    }

    /**
    * Appends given data to the file
    *
    * @param string $path   The path to the file that should be written
    * @param string $content The content of the file to be written
    * @param bool   $dryrun  The dryrun flag for the operation
    */
    public function appendToFile($path, $content, $dryrun = false){
        if($dryrun === true){
            $this->log("info", "DRYRUN: Appends the content to $path");

            return;
        }
        $this->fs->appendToFile($path, $content);
        $this->log("info", "Appends the content to $path");
    }
}