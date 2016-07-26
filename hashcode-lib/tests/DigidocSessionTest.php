<?php
use SK\Digidoc\DdocContainer;
use SK\Digidoc\Digidoc;
use SK\Digidoc\FileSystemDataFile;

class DigidocSessionTest extends TestCaseWithTemporaryFilesSupport {
    /** @var  \SK\Digidoc\DigidocSession */
    private $session;

    /**
     * @before
     */
    public function setUpSession () {
        $digidoc = new Digidoc();
        $this->session = $digidoc->createSession();
    }

    /**
     * @after
     */
    public function tearDownSession () {
        $this->session->end();
    }

    public function testBDocContainerFromStringToHashcodes () {
        $container = $this->session->containerFromString($this->getTestFile('test.bdoc'));
        $hashcodesContainer = $container->toHashcodeFormat();

        static::assertTrue($hashcodesContainer->isHashcodesFormat());
    }

    public function testDDocContainerFromStringToHashcodes () {
        $container = $this->session->containerFromString($this->getTestFile('test-ddoc13.ddoc'));
        $hashcodesContainer = $container->toHashcodeFormat();

        static::assertTrue($hashcodesContainer->isHashcodesFormat());
    }

    public function testBDocContainerFromStringToDataFiles () {
        $container = $this->session->containerFromString($this->getTestFile('test-hashcodes.bdoc'));
        $hashcodesContainer = $container->toDatafilesFormat(array (
            new FileSystemDataFile($this->getTestFilename('file1.txt')),
            new FileSystemDataFile($this->getTestFilename('file2.txt'))
        ));

        static::assertFalse($hashcodesContainer->isHashcodesFormat());
    }

    public function testDDocContainerFromStringToDataFiles () {
        $container = $this->session->containerFromString($this->getTestFile('doc-sample-hashcodes.ddoc'));
        $originalContainer = new DdocContainer($this->getTestFilename('doc-sample.ddoc'));
        $hashcodesContainer = $container->toDatafilesFormat($originalContainer->getDataFiles());

        static::assertFalse($hashcodesContainer->isHashcodesFormat());
    }

    /**
     * @expectedException SK\Digidoc\DigidocException
     */
    public function testInvalidContainerThrowsException () {
        $this->session->containerFromString('bla bla');
    }

    public function testEndSessionDeletesTempFiles () {
        $filename = $this->session->createFile();
        static::assertFileExists($filename);

        $this->session->end();
        static::assertFileNotExists($filename);
    }
}
