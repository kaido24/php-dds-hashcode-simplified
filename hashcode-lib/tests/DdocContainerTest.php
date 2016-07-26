<?php
use SK\Digidoc\DdocContainer;
use SK\Digidoc\DataFileInterface;
use SK\Digidoc\DdocDataFile;

class DdocContainerTest extends TestCaseWithTemporaryFilesSupport {

    public function testGetDataFiles() {
        $ddoc = new DdocContainer($this->getTestFilename('test-ddoc13.ddoc'));
        $datafiles = $ddoc->getDataFiles();
        self::assertCount(2, $datafiles);
        self::assertTrue($datafiles[0] instanceof DataFileInterface);
    }

    public function testWriteAsHashcodes() {
        $ddoc = new DdocContainer($this->getTestFilename('doc-sample.ddoc'));
        $fileWithHashcodes = $this->createTempFile();
        $ddoc->writeAsHashcodes($fileWithHashcodes);

        self::assertTrue(strpos(file_get_contents($fileWithHashcodes), 't8eRSrKTgR4PAAKTLYWGCjuTSJA=') !== false);
        self::assertXmlFileEqualsXmlFile($this->getTestFilename('doc-sample-hashcodes.ddoc'), $fileWithHashcodes);
    }

    public function testWriteWithDataFiles() {
        $ddoc = new DdocContainer($this->getTestFilename('doc-sample-hashcodes.ddoc'));
        $filename = $this->createTempFile();
        $ddoc->writeWithDataFiles(
            $filename,
            array(
                new DdocDataFile($this->getTestFilename('doc-sample.ddoc'), 'test.txt')
            )
        );

        self::assertXmlFileEqualsXmlFile($this->getTestFilename('doc-sample.ddoc'), $filename);
    }

    public function testDataFileToHashcode() {
        $fileContent = base64_decode('VGhpcyBpcyBhIHRlc3QgZmlsZQ0Kc2Vjb25kbGluZQ0KdGhpcmRsaW5l');
        $hash = DdocContainer::datafileHashcode('test.txt', 'D0', 'text/plain', $fileContent);

        self::assertEquals('t8eRSrKTgR4PAAKTLYWGCjuTSJA=', $hash);
    }

    /**
     * @expectedException SK\Digidoc\DigidocException
     *
     */
    public function testInvalidDdocVersion() {
        new DdocContainer($this->getTestFilename('test-ddoc10.ddoc'));
    }

    public function testSupportedFormatAndVersionFromString() {
        self::assertTrue(DdocContainer::isSupportedFormatAndVersion($this->getTestFile('test-ddoc11.ddoc')));
        self::assertTrue(DdocContainer::isSupportedFormatAndVersion($this->getTestFile('test-ddoc12.ddoc')));
        self::assertTrue(DdocContainer::isSupportedFormatAndVersion($this->getTestFile('test-ddoc13.ddoc')));
        self::assertFalse(DdocContainer::isSupportedFormatAndVersion($this->getTestFile('test-ddoc10.ddoc')));
    }
}



