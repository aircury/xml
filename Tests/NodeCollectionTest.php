<?php declare(strict_types=1);

namespace Aircury\Xml\Tests;

use Aircury\Xml\Node;
use Aircury\Xml\NodeCollection;
use PHPUnit\Framework\TestCase;

class NodeCollectionTest extends TestCase
{
    public function testOffsetSetWithoutName(): void
    {
        $wizard = new Node('wizard');
        $students = new NodeCollection([$wizard]);

        $this->expectException(\LogicException::class);

        $students['Harry Potter'] = new Node('wizard');
    }

    public function testValuesArray(): void
    {
        $wizard = new Node('wizard');
        $students = new NodeCollection([$wizard]);

        $this->assertCount(1, $students->toValuesArray());
    }

    public function testGetIndexBy(): void
    {
        $wizard = new Node('wizard', ['name' => 'Harry Potter']);
        $students = new NodeCollection([$wizard]);

        $students->indexByAttribute('name');

        $this->assertEquals('name', $students->getIndexBy());
    }

    public function testFilterByAttribute(): void
    {
        $wizard1 = new Node('wizard', ['name' => 'Harry Potter']);
        $wizard2 = new Node('wizard', ['name' => 'Hermione Granger']);
        $students = new NodeCollection([$wizard1, $wizard2]);

        $this->assertCount(1, $students->filterByAttribute('name', 'Harry Potter'));
    }

    public function testFilterByClosure(): void
    {
        $wizard1 = new Node('wizard', ['name' => 'Harry Potter', 'age' => 15]);
        $wizard2 = new Node('wizard', ['name' => 'Hermione Granger', 'age' => 16]);
        $students = new NodeCollection([$wizard1, $wizard2]);

        $students->indexByAttribute('name');

        $filtered = $students->filter(
            function (Node $node): bool {
                return $node['age'] >= 16;
            }
        );

        $this->assertCount(1, $filtered);
        $this->assertEquals(16, $filtered['Hermione Granger']['age']);
    }
}
