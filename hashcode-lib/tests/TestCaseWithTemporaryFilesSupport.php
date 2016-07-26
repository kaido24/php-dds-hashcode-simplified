<?php

class TestCaseWithTemporaryFilesSupport extends PHPUnit_Framework_TestCase {
    private $tempFilenames = array();

    private function getTestFilesBaseDirectory () {
        return __DIR__ . '/testfiles/';
    }

    protected function getTestFile ($filename) {
        return file_get_contents($this->getTestFilesBaseDirectory() . $filename);
    }

    protected function getTestFilename ($filename) {
        return $this->getTestFilesBaseDirectory() . $filename;
    }

    protected function createTempFile () {
        $filename = tempnam(sys_get_temp_dir(), 'ddoc');
        $this->tempFilenames[] = $filename;

        return $filename;
    }

    protected function setUp () {
        $this->tempFilenames = array ();
    }

    protected function tearDown () {
        foreach ($this->tempFilenames as $filename) {
            unlink($filename);
        }
    }

}