<?php declare(strict_types=1);

namespace Aircury\Xml;

class Node
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string[]
     */
    public $attributes;

    /**
     * @var string
     */
    private $contents;

    /**
     * @var NodeCollection
     */
    public $children;

    /**
     * Children of an specific name
     *
     * @var NodeCollection[]
     */
    public $namedChildren;

    public function __construct(string $name, array $attributes = [], string $contents = '')
    {
        $this->name       = $name;
        $this->attributes = $attributes;
        $this->contents   = trim($contents);
        $this->children   = new NodeCollection();
    }

    public function addChild(Node $child): Node
    {
        if (!isset($this->namedChildren[$child->name])) {
            $this->namedChildren[$child->name] = new NodeCollection();
        }

        $this->children[]                    = $child;
        $this->namedChildren[$child->name][] = $child;

        return $child;
    }

    public function indexByAttribute(string $childName, string $attribute): Node
    {
        if (!isset($this->namedChildren[$childName])) {
            $this->namedChildren[$childName] = new NodeCollection();
        }

        $this->namedChildren[$childName]->indexByAttribute($attribute);

        return $this;
    }

    public function getNamedChildren(string $childName, bool $createIfMissing = false): NodeCollection
    {
        if (!isset($this->namedChildren[$childName])) {
            if (!$createIfMissing) {
                return new NodeCollection();
            }

            $this->addChild(new Node($childName));
        }

        return $this->namedChildren[$childName];
    }

    public function getIndexedChild(string $childName, string $attributeValue, bool $createIfMissing = false): ?Node
    {
        $children = $this->getNamedChildren($childName, $createIfMissing);

        if (null === ($attribute = $children->getIndexBy())) {
            throw new \LogicException(sprintf('The NodeCollection %s is not indexed by any attribute', $childName));
        }

        if (!isset($children[$attributeValue])) {
            if (!$createIfMissing) {
                return null;
            }

            $node             = new Node($childName, [$attribute => $attributeValue]);
            $children[]       = $node;
            $this->children[] = $node;
        }

        return $this->namedChildren[$childName][$attributeValue];
    }

    public function writeXml(\XMLWriter $xmlWriter): void
    {
        $xmlWriter->startElement($this->name);

        foreach ($this->attributes as $name => $value) {
            $xmlWriter->writeAttribute($name, $value);
        }

        if ('' !== $this->contents) {
            $xmlWriter->writeRaw($this->contents);
        }

        foreach ($this->children->toArray() as $child) {
            $child->writeXml($xmlWriter);
        }

        $xmlWriter->endElement();
    }
}
