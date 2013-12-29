#PHP-ID3

PHP-ID3 makes use of native PHP to read [ID3 Tags](http://en.wikipedia.org/wiki/ID3‎) and thumbnail from a MP3 file. There have been many revisions to ID3 Tags specification; this program makes use of v3.2 of the [spec](http://id3.org/id3v2.3.0).

To read binary data more effectively, I have created a sclass, [BinaryFileReader](https://gist.github.com/shubhamjain/5964350), which reads data in named chunks. 

##How to Install

Into your composer.json

```json
{
    "require" : {
        "shubhamjain/php-id3": "dev-master"
    }
}
```

##How to Use

```php
<?php
//...
use PhpId3\Id3TagsReader;

//...
$id3 = new Id3TagsReader(fopen("Exodus - 06 - Piranha.mp3", "rb"));

$id3->readAllTags(); //Calling this is necesarry before others

foreach($id3->getId3Array() as $key => $value) {
	if( $key !== "APIC" ) { //Skip Image data
		echo $value["FullTagName"] . ": " . $value["Body"] . "<br />"; 
    }
}

list($mimeType, $image) = $id3->getImage();

file_put_contents("thumb.jpeg", $image ); //Note the image type depends upon MimeType

//...
```

##LICENSE

See ``LICENSE`` for more informations

##Feedback

If you used this project or liked it or have any doubt about the source, send your valuable thoughts at <shubham.jain.1@gmail.com>.
