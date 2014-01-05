<?php

/**
 * Writer ID3Tags and thumbnails.
 *
 * @author Shubham Jain <shubham.jain.1@gmail.com> 
 * @license MIT License
 */

include "ID3Tags.php";


Class ID3Tags_Writer {

	private $_FileHandle;
	private $_ID3Array;
	private $_Header;

	const CHUNK_SIZE = 10;

	public function __construct( $FileHandle, BinaryFileReader $inst)
	{

		$this->_FileHandle = $FileHandle;
		$this->_ID3Array = $inst->GetID3Arry();
		$this->_Header = $inst->GetHeader();
	}

	public function UpdateSize ( $size )
	{
		fseek( $this->_FileHandle, 5);
		fwrite( $this->_FileHandle, pack("N", $size));
	}

	public function ShiftContentDown ( $bytes, $point )
	{
		fseek( $this->_FileHandle, -self::CHUNK_SIZE, SEEK_END);
		
		while( $point )
		{
			$curPoint = ftell( $this->_FileHandle );

			if( ($point - self::CHUNK_SIZE) > 0)
			{
				$data = fread( $this->_FileHandle, self::CHUNK_SIZE );
				fseek( $this->_FileHandle, self::CHUNK_SIZE, )
				
			} else {
				$data = fread( $this->_FileHandle, $point );
			}

			

			$point -= self::CHUNK_SIZE;
		}
		

		$chunk = fread( $this->_FileHandle, self::CHUNK_SIZE );
		fseek( )
		fwrite( $this->_FileHandle, pack("N", $size));
	}

	public function UpdateTag ( $tagName, $tagBody )
	{
		$arr = $this->_ID3Array[ $tagName ];

		$this->UpdateSize( $this->_Header->SizeTag - $arr["Size"] + strlen( $tagBody ));

		fseek( $this->_FileHandle, $arr["Position"] );
		fwrite( $this->_FileHandle, pack("aNn", $tagName, strlen( $tagBody ), 0));

		$this->ShiftContent( ftell( $this->_FileHandle ), $arr["Size"] + strlen( $tagBody ))
	}

	public function WriteTag( $tagName, $tagBody ) 
	{
		assert( is_array( $this->_ID3Array) === TRUE );

		if( ! in_array( $tagName, $GLOBALS['ID3Tags'], TRUE ) )
			throw new InvalidArgumentException("Tag name must be a valid tag defined in ID3 Spec.");

		if( $tagName === "APIC" )
			throw new InvalidArgumentException("APIC cannot be used here. Use WriteImage() instead.");

		if( in_array($tagName, $this->_ID3Array, TRUE) )
			$this->UpdateTag( $tagName, $tagBody );
		else
			$this->CreateTag( $tagName, $tagBody);

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

