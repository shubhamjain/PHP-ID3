<?php

include "../ID3Tags_Reader.php";

$ID3 = new ID3Tags_Reader( fopen("Exodus - 06 - Piranha.mp3", "rb") );

$ID3->ReadAllTags(); //Calling this is necesarry before others

foreach($ID3->GetID3Array() as $key => $value)
{
	if( $key !== "APIC" ) //Skip Image data
		echo $value["FullTagName"] . ": " . $value["Body"] . "<br />"; 
}

list($MimeType, $image) = $ID3->GetImage();

file_put_contents("thumb.jpeg", $image ); //Note the image type depends upon MimeType

