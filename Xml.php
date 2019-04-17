<?php declare(strict_types=1);

namespace Aircury\Xml;

class Xml
{
    public static function parseString(string $xmlString): Node
    {
        return static::parseElement(simplexml_load_string($xmlString));
    }

    public static function parseFile(string $path): Node
    {
        return static::parseElement(simplexml_load_file($path));
    }

    private static function parseElement(\SimpleXMLElement $element): Node
    {
        $node = new Node(
            $element->getName(),
            (array) $element->attributes()['@attributes'] ?? [],
            $element->__toString()
        );

        foreach ($element->children() as $child) {
            /** @var \SimpleXMLElement $child */

            $node->addChild(static::parseElement($child));
        }

        return $node;
    }

    public static function dump(Node $node): string
    {
        $xmlWriter = new \XMLWriter();

        $xmlWriter->openMemory();
        $xmlWriter->setIndent(true);
        $xmlWriter->setIndentString('  ');
        $xmlWriter->startDocument('1.0', 'UTF-8');
        $node->writeXml($xmlWriter);
        $xmlWriter->endDocument();

        return str_replace('/>', ' />', $xmlWriter->outputMemory());
    }
}
