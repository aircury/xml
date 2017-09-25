<?php declare(strict_types=1);

namespace Aircury\Xml;

use Aircury\Collection\AbstractCollection;

class NodeCollection extends AbstractCollection
{
    /**
     * @var string
     */
    private $indexBy;

    public function getClass(): string
    {
        return Node::class;
    }

    public function offsetSet($offset, $element): void
    {
        if (null !== $offset && null === $this->indexBy) {
            throw new \LogicException('The NodeCollection is not indexed by an attribute so you cannot use an offset');
        }

        if (null === $this->indexBy) {
            parent::offsetSet($offset, $element);

            return;
        } else {
            if (!array_key_exists($this->indexBy, $element->attributes)) {
                throw new \LogicException(
                    sprintf(
                        'A NodeCollection was requested to index by the attribute %s, but found a node that doesn\'t have it',
                        $this->indexBy
                    )
                );
            }

            parent::offsetSet($element->attributes[$this->indexBy], $element);
        }
    }

    public function offsetGet($offset): Node
    {
        return $this->doOffsetGet($offset);
    }

    /**
     * @return Node[]
     */
    public function toArray(): array
    {
        return $this->getElements();
    }

    /**
     * @return Node[]
     */
    public function toValuesArray(): array
    {
        return parent::toValuesArray();
    }

    public function first(): Node
    {
        return $this->doGetFirst();
    }

    public function indexByAttribute(string $attribute): NodeCollection
    {
        $nodes         = $this->toArray();
        $this->indexBy = $attribute;

        $this->setElements([]);

        foreach ($nodes as $node) {
            if (!array_key_exists($this->indexBy, $node->attributes)) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'A NodeCollection was requested to index by the attribute %s, but found a node that doesn\'t have it',
                        $attribute
                    )
                );
            }

            if ($this->offsetExists($node->attributes[$attribute])) {
                throw new \LogicException(
                    sprintf(
                        'In order to index a NodeCollection by the attribute %s, they must be unique. The attribute \'%s\' is repeated',
                        $this->indexBy,
                        $node->attributes[$attribute]
                    )
                );
            }

            $this[] = $node;
        }

        return $this;
    }

    public function getIndexBy(): ?string
    {
        return $this->indexBy;
    }

    public function filterByClosure(callable $filter): NodeCollection
    {
        $nodes = new NodeCollection();

        foreach ($this->toArray() as $result) {
            if ($filter($result)) {
                $nodes[] = $result;
            }
        }

        if (null !== $this->indexBy) {
            $nodes->indexByAttribute($this->indexBy);
        }

        return $nodes;
    }

    public function filterByAttribute(string $attribute, string $value): NodeCollection
    {
        return $this->filterByClosure(
            function (Node $node) use ($attribute, $value) {
                return array_key_exists($attribute, $node->attributes) && $node->attributes[$attribute] === $value;
            }
        );
    }

    /**
     * @param string[] $attributes
     */
    public function filterByAttributes(array $attributes): NodeCollection
    {
        return $this->filterByClosure(
            function (Node $node) use ($attributes) {
                return array_intersect_key($node->attributes, $attributes) == $attributes;
            }
        );
    }
}
