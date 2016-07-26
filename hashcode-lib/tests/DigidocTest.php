<?php
use SK\Digidoc\Digidoc;

class DigidocTest extends PHPUnit_Framework_TestCase
{

    public function testConfigureTempDir()
    {
        $dir = sys_get_temp_dir() . "/" . uniqid();

        $digidoc = new Digidoc(array(
            Digidoc::TEMPORARY_DIR => $dir
        ));
        $session = $digidoc->createSession();
        $file = $session->createFile();
        $this->assertNotFalse(
            realpath($dir),
            "Custom session directory does not exist (should be created automatically)."
        );
        $this->assertStringStartsWith(realpath($dir), realpath($file), "Created file is not in specified temp dir.");
        $session->end();
        rmdir($dir);
    }

    public function testDeleteLocalFiles()
    {

        $dir = sys_get_temp_dir() . "/" . uniqid();

        $digidoc = new Digidoc(array(
            Digidoc::TEMPORARY_DIR => $dir
        ));
        $digidoc->createSession()->createFile();
        $digidoc->createSession()->createFile();
        $digidoc->createSession()->createFile();

        $this->assertTrue(file_exists($dir));

        $digidoc->deleteLocalTempFiles();

        $iterator = new \FilesystemIterator($dir);
        $isDirEmpty = !$iterator->valid();
        $this->assertTrue($isDirEmpty);

        rmdir($dir);

    }
}