<?php
namespace SK\Digidoc;

/**
 * DataFile representing file inside a ddoc file.
 */
class DdocDataFile implements DataFileInterface
{

    private $ddocFilename;
    private $filename;

    private $contentRead = false;
    private $content;
    private $dataFileContentOnMultipleLines = Digidoc::DDOC_DATA_FILE_CHUNK_SPLIT;
    private $rawContent;

    /**
     * Constructor using ddoc filename and file name.
     *
     * @param string $ddocFilename ddoc container file name
     * @param string $filename     file name inside ddoc container
     */
    public function __construct($ddocFilename, $filename)
    {
        $this->ddocFilename = $ddocFilename;
        $this->filename = $filename;
    }

    /**
     * @internal public for testing
     * @return string
     */
    public function hashcode()
    {
        return base64_encode(sha1($this->readXmlElementCanonized(), true));
    }

    /**
     * @internal public for testing
     * @return string
     */
    public function readXmlElementCanonized()
    {
        return $this->handleDataFileElement(function (\XMLReader $xmlReader) {
            $dom = new \DOMDocument();
            $node = $xmlReader->expand();
            $dom->appendChild($node);

            return $node->C14N();
        });
    }

    /**
     * Get {DdocDataFile} name
     *
     * @return string
     */
    public function getName()
    {
        return $this->filename;
    }

    /**
     * Get {DdocDataFile{ file size in bytes
     *
     * @return int
     */
    public function getSize()
    {
        return strlen($this->getContent());
    }

    /**
     * Get {DdocDataFile} file contents as {string}
     *
     * @return string
     */
    public function getContent()
    {
        if (!$this->contentRead) {
            $this->content = $this->readContent();
        }

        return $this->content;
    }

    /**
     * Is data file encoded as multi line Base64 string or one line
     *
     * @return bool
     */
    public function isMMultiLine()
    {
        return $this->dataFileContentOnMultipleLines;
    }

    /**
     * Get {DdocDataFile} content as Base64 encoded string
     *
     * @return mixed
     */
    public function getRawContent()
    {
        $this->readRawContent();

        return $this->rawContent;
    }

    private function handleDataFileElement(\Closure $callback)
    {
        $xmlReader = new \XMLReader();
        $xmlReader->open($this->ddocFilename);
        $result = null;
        while ($xmlReader->read()) {
            if ($this->isDataFileElement($xmlReader) && $this->isRequestedDataFileFilename($xmlReader)) {
                $this->rawContent = $xmlReader->readOuterXml();
                $result = $callback($xmlReader);
                break;
            }
        }
        $xmlReader->close();

        return $result;
    }

    /**
     * @param $xmlReader
     *
     * @return bool
     */
    private function isDataFileElement($xmlReader)
    {
        return $xmlReader->localName === 'DataFile' && $xmlReader->nodeType === \XMLReader::ELEMENT;
    }

    /**
     * @param $xmlReader
     *
     * @return bool
     */
    private function isRequestedDataFileFilename(\XMLReader $xmlReader)
    {
        return $xmlReader->getAttribute('Filename') === $this->filename;
    }

    private function readContent()
    {
        $content = $this->readRawContent();
        $this->dataFileContentOnMultipleLines = substr_count($content, "\n") > 1;

        return base64_decode($content);
    }

    private function readRawContent()
    {
        return $this->handleDataFileElement(function (\XMLReader $xmlReader) {
            $content = $xmlReader->readInnerXml();

            return $content;
        });
    }
}
