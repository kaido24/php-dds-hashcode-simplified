<?php
use SK\Digidoc\BdocDataFile;
use SK\Digidoc\DdocDataFile;
use SK\Digidoc\FileSystemDataFile;
use SK\Digidoc\DataFileInterface;

class DataFileTest extends TestCaseWithTemporaryFilesSupport
{

    public function testBdocDataFile()
    {
        $dataFile = new BdocDataFile($this->getTestFilename("test.bdoc"), "file1.txt");
        $this->assertFile1($dataFile);
    }

    public function testFileSystemDataFile()
    {
        $dataFile = new FileSystemDataFile($this->getTestFilename("file1.txt"));
        $this->assertFile1($dataFile);
    }

    public function testDdocDataFile()
    {
        $dataFile = new DdocDataFile($this->getTestFilename("test-ddoc13.ddoc"), "file1.txt");
        $this->assertFile1($dataFile);
    }

    private function assertFile1(DataFileInterface $dataFile)
    {
        $this->assertNotNull($dataFile);
        $this->assertEquals("file1.txt", $dataFile->getName());
        $this->assertEquals(14, $dataFile->getSize());
        $this->assertEquals("this is file1\n", $dataFile->getContent());
    }
}
