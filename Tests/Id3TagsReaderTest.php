<?php

namespace PhpId3\Tests;

use PhpId3\Id3TagsReader;

class GenerateCvCommandTest extends \PHPUnit_Framework_TestCase
{
    private $id3;

    private $mp3File = "/TestFiles/Exodus - 06 - Piranha.mp3";

    private $albumCover = "/TestFiles/thumb.jpeg";

    protected function setUp()
    {
        $this->id3 = new Id3TagsReader(fopen(__DIR__ . $this->mp3File, "rb"));
        $this->id3->readAllTags();
    }

    public function testGetImage()
    {
        $image = $this->id3->getImage();

        $this->assertEquals("mage/jpg", $image[0]);
        $this->assertEquals(file_get_contents(__DIR__ . $this->albumCover), $image[1]);
    }

    public function testGetId3TagsArray()
    {
        $id3Tags = $this->id3->getId3Array();

        $this->assertEquals($id3Tags["TIT2"]["body"], 'Piranha');
        $this->assertEquals($id3Tags["TRCK"]["body"], '6');
        $this->assertEquals($id3Tags["TCON"]["body"], 'Heavy Metal');
        $this->assertEquals($id3Tags["TALB"]["body"], 'Bonded by Blood');
        $this->assertEquals($id3Tags["TYER"]["body"], '1985');
        $this->assertEquals($id3Tags["TPE1"]["body"], 'Exodus');
        $this->assertEquals($id3Tags["TLEN"]["body"], '228963');
    }

}
