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
    private $validMp3 = TRUE;

    public function __construct($fileHandle)
    {
        $this->fileReader = new BinaryFileReader($fileHandle, array(
            "id3" => array(BinaryFileReader::FIXED, 3),
            "version" => array(BinaryFileReader::FIXED, 2),
            "flag" => array(BinaryFileReader::FIXED, 1),
            "sizeTag" => array(BinaryFileReader::FIXED, 4, BinaryFileReader::INT),
        ));

        $data = $this->fileReader->read();

        if( $data->id3 !== "ID3")
        {
            throw new \Exception("The MP3 file contains no valid ID3 Tags.");
            $this->validMp3 = FALSE;
        }

    }

    public function readAllTags()
    {
        assert( $this->validMp3 === TRUE);

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

            $body = $file_data->body;

            // If frame is a text frame then we have to consider 
            // encoding as shown in spec section 4.2
            if( $file_data->frameId[0] === "T" )
            {
                // First character determines the encoding, 1 = ISO-8859-1, 0 = UTF - 16
                if( intval(bin2hex($body[0]), 16) === 1)
                    $body = mb_convert_encoding(substr($body, 1), 'UTF-8', 'UTF-16'); // Convert UTF-16 to UTF-8 to compatible with current browsers
            }

            $this->id3Array[$file_data->frameId] = array(
                "fullTagName" => $id3Tags[$file_data->frameId],
                "position" => $bytesPos,
                "size" => $file_data->size,
                "body" => $body,
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
