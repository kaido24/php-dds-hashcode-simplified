<?php
use SK\Digidoc\BdocContainer;
use SK\Digidoc\DataFileInterface;
use SK\Digidoc\FileSystemDataFile;
use SK\Digidoc\Digidoc;

class BdocContainerTest extends TestCaseWithTemporaryFilesSupport {
    public function testReadDataFilesFromBdoc()
    {
        $bdoc = new BdocContainer($this->getTestFilename('test.bdoc'));
        $datafiles = $bdoc->getDataFiles();
        $this->assertCount(2, $datafiles);
        $this->assertTrue($datafiles[0] instanceof DataFileInterface);
    }

    public function testConvertBdocToHashcodes()
    {
        $fileWithHashcodes = $this->createBdocHashcodes();
        $this->assertFileExistsInZip($fileWithHashcodes, "META-INF/hashcodes-sha256.xml");
        $this->assertFileExistsInZip($fileWithHashcodes, "META-INF/hashcodes-sha512.xml");
        $this->assertFileNotExistsInZip($fileWithHashcodes, "file1.txt");
        $this->assertFileNotExistsInZip($fileWithHashcodes, "file2.txt");
    }

    private function createBdocHashcodes()
    {
        $bdoc = new BdocContainer($this->getTestFilename('test.bdoc'));
        $fileWithHashcodes = $this->createTempFile();
        $bdoc->writeAsHashcodes($fileWithHashcodes);
        return $fileWithHashcodes;
    }

    public function testConvertHashcodestToBdoc()
    {
        $bdocFile = $this->createBdoc();
        $this->assertFileNotExistsInZip($bdocFile, "META-INF/hashcodes-sha256.xml");
        $this->assertFileNotExistsInZip($bdocFile, "META-INF/hashcodes-sha512.xml");
        $this->assertFileExistsInZip($bdocFile, "file1.txt");
        $this->assertFileExistsInZip($bdocFile, "file2.txt");
    }

    private function createBdoc()
    {
        $bdocHashodes = new BdocContainer($this->getTestFilename('test-hashcodes.bdoc'));
        $bdocFile = $this->createTempFile();
        $bdocHashodes->writeWithDataFiles(
            $bdocFile,
            array(
                new FileSystemDataFile($this->getTestFilename("file1.txt")),
                new FileSystemDataFile($this->getTestFilename("file2.txt"))
            )
        );
        return $bdocFile;
    }

    private function assertFileExistsInZip($zipArchiveFilename, $filename)
    {
        $zip = new ZipArchive();
        $zip->open($zipArchiveFilename);
        $this->assertNotFalse($zip->locateName($filename), "File does not exist in zip archive: $filename");
        $zip->close();
    }

    private function assertFileNotExistsInZip($zipArchiveFilename, $filename)
    {
        $zip = new ZipArchive();
        $zip->open($zipArchiveFilename);
        $this->assertFalse($zip->locateName($filename), "File should not exist in zip archive: $filename");
        $zip->close();
    }

    public function testBdocComment()
    {
        $file = $this->createBdoc();
        $this->assertArchiveComment($file);
    }

    public function testBdocHashcodesComment()
    {
        $file = $this->createBdocHashcodes();
        $this->assertArchiveComment($file);
    }

    private function assertArchiveComment($filename)
    {
        $zip = new ZipArchive();
        $zip->open($filename);

        // other clients write entry comments instead of archive comment
        // $this->assertEquals($comment, $zip->getArchiveComment());

        for ($i = 0; $i < $zip->numFiles; $i++) {
            echo $this->assertEquals($this->expectedComment(), $zip->getCommentIndex($i));
        }
        $zip->close();
    }

    private function expectedComment()
    {
        return sprintf(
            "dds-hashcode %s - PHP %s, %s %s %s",
            Digidoc::version(),
            phpversion(),
            php_uname("s"),
            php_uname("r"),
            php_uname("v")
        );
    }

    public function testDatafileHashcode()
    {
        $this->assertEquals(
            "/saLFxLxPdMSdzQC4oJxJhRYkwOpoOGVLmE+aumGlN4=",
            BdocContainer::datafileHashcode($this->getTestFile("file1.txt"))
        );
    }
}
