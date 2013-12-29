<?php

namespace PhpId3;

use PhpId3\Id3Tags;
use PhpId3\BinaryFileReader;

/**
 * Read ID3Tags and thumbnails.
 *
 * @author Shubham Jain <shubham.jain.1@gmail.com>
 * @license MIT License
 */
class Id3TagsReader
{

    private $fileReader;
    private $id3Array;

    public function __construct($fileHandle)
    {
        $this->fileReader = new BinaryFileReader($fileHandle, array(
            "id3" => array(BinaryFileReader::FIXED, 3),
            "version" => array(BinaryFileReader::FIXED, 2),
            "flag" => array(BinaryFileReader::FIXED, 1),
            "sizeTag" => array(BinaryFileReader::FIXED, 4, BinaryFileReader::INT),
        ));

        $this->fileReader->read();
    }

    public function readAllTags()
    {
        $bytesPos = 10; //From headers

        $this->fileReader->setMap(array(
            "frameId" => array(BinaryFileReader::FIXED, 4),
            "size" => array(BinaryFileReader::FIXED, 4, BinaryFileReader::INT),
            "flag" => array(BinaryFileReader::FIXED, 2),
            "body" => array(BinaryFileReader::SIZE_OF, "size"),
        ));

        $id3Tags = Id3Tags::getId3Tags();

        while (($file_data = $this->fileReader->read())) {

            if (!in_array($file_data->frameId, array_keys($id3Tags))) {
                break;
            }

            $this->id3Array[$file_data->frameId] = array(
                "fullTagName" => $id3Tags[$file_data->frameId],
                "position" => $bytesPos,
                "size" => $file_data->size,
                "body" => $file_data->body,
            );

            $bytesPos += 4 + 4 + 2 + $file_data->size;
        }
        return $this;
    }

    public function getId3Array()
    {
        return $this->id3Array;
    }

    public function getImage()
    {
        $fp = fopen('data://text/plain;base64,' . base64_encode($this->id3Array["APIC"]["body"]), 'rb'); //Create an artificial stream from Image data

        $fileReader = new BinaryFileReader($fp, array(
            "textEncoding" => array(BinaryFileReader::FIXED, 1),
            "mimeType" => array(BinaryFileReader::NULL_TERMINATED),
            "fileName" => array(BinaryFileReader::NULL_TERMINATED),
            "contentDesc" => array(BinaryFileReader::NULL_TERMINATED),
            "binaryData" => array(BinaryFileReader::EOF_TERMINATED)
            )
        );

        $imageData = $fileReader->read();

        return array($imageData->mimeType, $imageData->binaryData);
    }
}
