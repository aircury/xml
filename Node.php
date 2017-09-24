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

    public function getNamedChildren(string $childName, array $attributes, bool $createIfMissing = false): NodeCollection
    {
        if (!isset($this->namedChildren[$childName])) {
            if (!$createIfMissing) {
                return new NodeCollection();
            }

            $this->addChild(new Node($childName, $attributes));

            return $this->namedChildren[$childName];
        }

        $matchingNodes = $this->namedChildren[$childName]->filterByAttributes($attributes);

        if ($matchingNodes->isEmpty()) {
            $this->addChild(new Node($childName, $attributes));

            return $this->namedChildren[$childName]->filterByAttributes($attributes);
        }

        return $matchingNodes;
    }

    public function getNamedChild(string $childName, array $attributes, bool $createIfMissing = false): Node
    {
        $namedChildren = $this->getNamedChildren($childName, $attributes, $createIfMissing);

        if ($namedChildren->count() !== 1) {
            throw new \LogicException(
                sprintf(
                    'It was expecting exactly one match, but got %d. Maybe you didn\'t ask to create it if missing?',
                    $namedChildren->count()
                )
            );
        }

        return $namedChildren->first();
    }

    public function getIndexedChild(string $childName, string $attributeValue, bool $createIfMissing = false): ?Node
    {
        $children = $this->getNamedChildren($childName, [], $createIfMissing);

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
        $this->attributes = array_map('strval', $this->attributes);

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
