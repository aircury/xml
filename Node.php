<?php declare(strict_types=1);

namespace Aircury\Xml;

class Node implements \ArrayAccess
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
    public $contents;

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

    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->attributes);
    }

    public function offsetGet($offset)
    {
        if (!array_key_exists($offset, $this->attributes)) {
            throw new \LogicException(sprintf('The Node doesn\'t have an attribute with the name %s', $offset));
        }

        return $this->attributes[$offset];
    }

    public function offsetSet($offset, $value): void
    {
        $this->attributes[$offset] = $value;
    }

    public function offsetUnset($offset): void
    {
        unset($this->attributes[$offset]);
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

    public function getNamedChildren(
        string $childName,
        array $attributes = [],
        bool $createIfMissing = false
    ): NodeCollection {
        if (!isset($this->namedChildren[$childName])) {
            if (!$createIfMissing) {
                return new NodeCollection();
            }

            $this->addChild(new Node($childName, $attributes));

            return $this->namedChildren[$childName];
        }

        if (empty($attributes)) {
            return $this->namedChildren[$childName];
        }

        $matchingNodes = $this->namedChildren[$childName]->filterByAttributes($attributes);

        if ($matchingNodes->isEmpty()) {
            if ($createIfMissing) {
                $this->addChild(new Node($childName, $attributes));
            }

            return $this->namedChildren[$childName]->filterByAttributes($attributes);
        }

        return $matchingNodes;
    }

    /**
     * @param string[] $attributes
     */
    public function getNamedChild(string $childName, array $attributes = [], bool $createIfMissing = true): Node
    {
        $namedChildren = $this->getNamedChildren($childName, $attributes, $createIfMissing);

        if (1 !== $namedChildren->count()) {
            throw new \LogicException(
                sprintf(
                    'It was expecting exactly one match of \'%s\' %s, but got %d. Maybe you didn\'t ask to create it if missing?',
                    $childName,
                    json_encode($attributes),
                    $namedChildren->count()
                )
            );
        }

        return $namedChildren->first();
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

    public function dump(): string
    {
        $xmlWriter = new \XMLWriter();
        $xmlWriter->openMemory();
        $xmlWriter->setIndent(true);
        $xmlWriter->setIndentString('  ');
        $this->writeXml($xmlWriter);
        $xmlWriter->endDocument();

        return trim(str_replace('/>', ' />', $xmlWriter->outputMemory()));
    }
}
