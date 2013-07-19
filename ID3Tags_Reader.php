<?php

/**
 * Read ID3Tags and thumbnails.
 *
 * @author Shubham Jain <shubham.jain.1@gmail.com> 
 * @license MIT License
 */

include "ID3Tags.php";
include "BinaryFileReader.php";

Class ID3Tags_Reader {

	private $_FileReader;
	private $_ID3Array;

	public function __construct( $FileHandle )
	{
		$this->_FileReader = new BinaryFileReader($FileHandle, array(
			"ID3" => array(BinaryFileReader::FIXED, 3),
			"Version" => array(BinaryFileReader::FIXED, 2),
			"Flag" => array(BinaryFileReader::FIXED, 1),
			"SizeTag" => array(BinaryFileReader::FIXED, 4, BinaryFileReader::INT),
		));		

		$this->_FileReader->Read();
	}

	public function ReadAllTags() {
		
		$bytesPos = 10; //From headers

		$this->_FileReader->SetMap( array(
			"FrameID" => array(BinaryFileReader::FIXED, 4),
			"Size" => array(BinaryFileReader::FIXED, 4, BinaryFileReader::INT),
			"Flag" => array(BinaryFileReader::FIXED, 2),
			"Body" => array(BinaryFileReader::SIZE_OF, "Size"),
		));

		while( ($file_data = $this->_FileReader->Read()) )
		{
			if( ! in_array( $file_data->FrameID, array_keys($GLOBALS['ID3Tags']) ) )
				break;
			
			$this->_ID3Array[ $file_data->FrameID ] = array(
					 "FullTagName" => $GLOBALS['ID3Tags'][  $file_data->FrameID ],
					 "Position" => $bytesPos,
					 "Size" => $file_data->Size,
					 "Body" => $file_data->Body,
				);

			$bytesPos += 4 + 4 + 2 + $file_data->Size;		
		}

		return $this;
	}

	public function GetID3Array() {
		return $this->_ID3Array;
	}

	public function GetImage() {
		$fp = fopen('data://text/plain;base64,'.base64_encode($this->_ID3Array["APIC"]["Body"]), 'rb'); //Create an artificial stream from Image data

		$fileReader = new BinaryFileReader( $fp, array(
			"TextEncoding" => array(BinaryFileReader::FIXED, 1),
			"MimeType" => array(BinaryFileReader::NULL_TERMINATED),
			"FileName" => array(BinaryFileReader::NULL_TERMINATED),
			"ContentDesc" => array(BinaryFileReader::NULL_TERMINATED),
			"BinaryData" => array(BinaryFileReader::EOF_TERMINATED)) );

		
		$imageData = $fileReader->Read();

		return array( $imageData->MimeType, $imageData->BinaryData );
	}
}

