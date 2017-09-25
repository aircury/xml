<?php declare(strict_types=1);

namespace Aircury\Xml\Tests;

use Aircury\Xml\Node;
use Aircury\Xml\Xml;
use PHPUnit\Framework\TestCase;

class XmlTest extends TestCase
{
    const SAMPLE_XML = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<family name="Aircury family">
  <father is="strict father">Jose</father>
  <son is="apprentice 1">Pablo</son>
  <son is="apprentice 2">Ivan</son>
</family>

XML;

    public function testParseStringToXml(): void
    {
        $xmlString = <<< xml
<?xml version="1.0" encoding="UTF-8"?>
<note>
  <to>Tove</to>
  <from>Jani</from>
  <heading>Reminder</heading>
  <body>Don't forget me this weekend!</body>
</note>

xml;
        $node      = Xml::parseString($xmlString);

        $this->assertEquals($xmlString, Xml::dump($node));
    }

    public function testBasicXmlConstruction(): void
    {
        $node = new Node('family', ['name' => 'Aircury family']);

        $node->addChild(new Node('father', ['is' => 'strict father'], 'Jose'));
        $node->addChild(new Node('son', ['is' => 'apprentice 1'], 'Pablo'));
        $node->addChild(new Node('son', ['is' => 'apprentice 2'], 'Ivan'));

        $this->assertEquals(self::SAMPLE_XML, Xml::dump($node));
    }

    public function testParseFile(): void
    {
        file_put_contents('/tmp/test.xml', self::SAMPLE_XML);

        $node = Xml::parseFile('/tmp/test.xml');

        $this->assertInstanceOf(Node::class, $node);
    }
}
