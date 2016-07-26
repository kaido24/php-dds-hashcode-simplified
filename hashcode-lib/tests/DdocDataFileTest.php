<?php
use SK\Digidoc\DdocDataFile;

class DdocDataFileTest extends TestCaseWithTemporaryFilesSupport
{

    public function testDdocReadXml()
    {

        $filename = $this->getTestFilename("test-ddoc13.ddoc");
        $dataFile = new DdocDataFile($filename, "file1.txt");
        $this->assertXmlStringEqualsXmlString(
            substr($this->getTestFile("test-ddoc13.ddoc"), 127, 188),
            $dataFile->readXmlElementCanonized()
        );
    }

    public function testHashcode()
    {
        $datafile = new DdocDataFile($this->getTestFilename('doc-sample.ddoc'), 'test.txt');
        $this->assertEquals('t8eRSrKTgR4PAAKTLYWGCjuTSJA=', $datafile->hashcode());
    }
}