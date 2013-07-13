<?php

/**
 * A simple class to read variable byte length binary data. 
 * This is basically is a better replacement for unpack() function
 * which creates a very large associative array.
 *
 * @author Shubham Jain <shubham.jain.1@gmail.com> 
 * @example https://github.com/shubhamjain/PHP-ID3
 * @license MIT License
 */

class BinaryFileReader {
	const SIZE_OF = 1; 		   //size of block depends upon the variable defined in the next array element.
	const NULL_TERMINATED = 2; //Block is read until NULL is encountered.
	const EOF_TERMINATED = 3;  //Block is read until EOF  is encountered.
	const FIXED = 4;  	   	   //Block size is fixed.

	const INT = 5;  	//Datatypes to transform the read block
	const FLOAT = 6;
	
	private $_fp; //file handle to read data
	private $_map; //Associative array of Varaibles and their info ( TYPE, SIZE, DATA_TYPE)
				  // In special cases it can be an array to handle different
				  // Types of block data lengths

	public function __construct ( $fp, array $map )
	{
		$this->_fp = $fp;
		$this->SetMap( $map );
		
	}

	public function SetMap ( $map ) 
	{
		$this->_map = $map;

		foreach ($map as $key => $size) {
			$this->$key = NULL; //Create variables from keys of $map
		}
	}

	public function Read() 
	{	
		if( feof($this->_fp) )
			return false;

		foreach ($this->_map as $key => $info) 
		{		
				switch ( $info[0] ) 
				{					
					case self::NULL_TERMINATED:			
						while( (int)bin2hex(($ch = fgetc( $this->_fp ))) !== 0 ) 
							$this->$key .= $ch;	break;
						
					case self::EOF_TERMINATED:		
						while( !feof($this->_fp) ) 
							$this->$key .= fgetc( $this->_fp );	break;

					case self::SIZE_OF:					
						if( !( $info[1] = $this->$info[1] )) //If the variable is not an integer retunn false
							return false;

					default:

						$this->$key = fread($this->_fp, $info[1]); //Read as string					
					
				}

				if( isset($info[2]) )
					switch ( $info[2] )
					{
						case self::INT:
							$this->$key = intval(bin2hex($this->$key), 16); break;
						case self::FLOAT:
							$this->$key = floatval(bin2hex($this->$key), 16);
					} 
		}

		return $this;
	}

}