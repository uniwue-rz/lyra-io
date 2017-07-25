<?php
/**
* The test class for the Base class
*
* @author Pouyan Azari <pouyan.azari@uni-wuerzburg.de>
* @license MIT
*/

namespace De\Uniwue\RZ\Lyra\IO;

// This is a simple logger for the test
include 'Logger.php';

class IOTest extends \PHPUnit_Framework_TestCase{
    
    // Sets up the given test
    public function setUp(){
        global $configRoot;
        $this->root = $configRoot;
        $this->logger = new Logger();
    }

    /** 
    * Tests the constructor
    *
    */
    public function testInit(){
        $io = new IO($this->logger);
        $this->assertEquals(get_class($io),"De\Uniwue\RZ\Lyra\IO\IO");
    }

    /**
    * Tests the mkdir
    *
    */
    public function testMkdir(){
        $io = new IO($this->logger);
        $path = $this->root."testDirMk";
        $io->remove($path);
        $io->mkdir($path);
        $io->mkdir($path, 0777, true);
        $this->assertTrue(\file_exists($path));
        $io->remove($path);
    }

    /**
    * Test copy
    *
    */
    public function testCopy(){
        $io = new IO($this->logger);
        $path = $this->root."testDirMk";
        $source = $this->root."testDir";
        $io->copy($source, $path, 0777, true, true);
        $io->copy($source, $path);
        $this->assertTrue(\file_exists($path."/testDir2/testFile"));
        $io->remove($path);
    }

    /**
    * Tests file rename
    *
    **/
    public function testRename(){
        $io = new IO($this->logger);
        $path = $this->root."testDirMove";
        $source = $this->root."testDirMv";
        $io->rename($source, $path);
        $this->assertTrue(\file_exists($path."/testDir2/testFile"));
        $io->rename($path, $source);
        $this->assertTrue(\file_exists($source."/testDir2/testFile"));
    }

    /**
    * Tests dumping to a given file
    *
    */
    public function testDumpFile(){
        $io = new IO($this->logger);
        $path = $this->root."TestFileDump";
        $content = "TEST";
        $io->dumpFile($path, $content);
        $this->assertEquals(\file_get_contents($path), $content);
        \unlink($path);
    }

    /**
    * Tests appending to a given file
    *
    */
    public function testAppendFile(){
        $io = new IO($this->logger);
        $path = $this->root."TestFileDump";
        $content = "TEST";
        $io->dumpFile($path, $content);
        $io->appendToFile($path, $content);
        $content = $content.$content;
        $this->assertEquals(\file_get_contents($path), $content);
        \unlink($path);
    }

    /**
    * Tests removing give file
    *
    *
    */
    public function testRemoveFile(){
        $io = new IO($this->logger);
        $path = $this->root."TestFileDump";
        $content = "TEST";
        $io->dumpFile($path, $content);
        $io->remove($path, true);
        $io->remove($path);
        $this->assertFalse(\file_exists($path));
    }
}