<?php
namespace SK\Digidoc;

class HashcodesXmlTest extends \TestCaseWithTemporaryFilesSupport
{
    const TEST_FILENAME = 'file1.txt';
    const TEST_FILENAME_2 = 'file2.txt';
    const HASHING_ALGORITHM = 'sha256';
    const GENERATED_HASH = '/saLFxLxPdMSdzQC4oJxJhRYkwOpoOGVLmE+aumGlN4=';
    const XML_ATTRIBUTE_HASH_VALUE = 'a123';

    const TEST_XML = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<hashcodes>
	 <file-entry full-path="file1.txt" hash="a123" size="11"/>
	 <file-entry full-path="file2.txt" hash="b123" size="22"/>
</hashcodes>
XML;

    const TEST_XML2 = <<<XML
<?xml version="1.0"?>
<hashcodes>
    <file-entry full-path="file1.txt" hash="/saLFxLxPdMSdzQC4oJxJhRYkwOpoOGVLmE+aumGlN4=" size="14"/>
    <file-entry full-path="file2.txt" hash="tHXulJujrTufCLdVlLk8P1ZmiwPrzy9teXMTNEJNqeM=" size="14"/>
</hashcodes>
XML;

    public function testParseSimple()
    {
        $fileEntries = HashcodesXml::parse(self::TEST_XML);
        self::assertThat($fileEntries[0]->getFullPath(), self::equalTo(self::TEST_FILENAME));
        self::assertThat($fileEntries[0]->getHash(), self::equalTo(self::XML_ATTRIBUTE_HASH_VALUE));
        self::assertThat($fileEntries[1]->getSize(), self::equalTo(22));
    }

    public function testWriteAndParseXml()
    {
        $fileEntries = array();
        $fileEntries[] = new HashcodesFileEntry(self::TEST_FILENAME, self::XML_ATTRIBUTE_HASH_VALUE, 22);
        $fileEntries[] = new HashcodesFileEntry(self::TEST_FILENAME_2, self::XML_ATTRIBUTE_HASH_VALUE, 33);
        $xml = HashcodesXml::write($fileEntries);

        $fileEntries = HashcodesXml::parse($xml);

        self::assertThat($fileEntries[0]->getFullPath(), self::equalTo(self::TEST_FILENAME));
        self::assertThat($fileEntries[0]->getHash(), self::equalTo(self::XML_ATTRIBUTE_HASH_VALUE));
        self::assertThat($fileEntries[1]->getSize(), self::equalTo(33));
    }


    public function testConvertDataFileToFileEntry()
    {

        $dataFile = new FileSystemDataFile($this->getTestFilename(self::TEST_FILENAME));
        $fileEntry = HashcodesXml::convertDataFileToFileEntry($dataFile, self::HASHING_ALGORITHM);

        self::assertEquals(self::TEST_FILENAME, $fileEntry->getFullPath());
        self::assertEquals(14, $fileEntry->getSize());
        self::assertEquals(self::GENERATED_HASH, $fileEntry->getHash());
    }

    public function testWriteDataFilesToXml()
    {
        $xml = HashcodesXml::dataFilesToHashcodesXml(
            array(
                new FileSystemDataFile($this->getTestFilename(self::TEST_FILENAME)),
                new FileSystemDataFile($this->getTestFilename(self::TEST_FILENAME_2))
            ),
            self::HASHING_ALGORITHM
        );

        self::assertNotNull($xml);
        self::assertXmlStringEqualsXmlString(self::TEST_XML2, $xml);
    }

}
