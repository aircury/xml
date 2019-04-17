<?php declare(strict_types=1);

namespace Aircury\Xml\Tests;

use Aircury\Xml\Node;
use PHPUnit\Framework\TestCase;

class NodeTest extends TestCase
{
    public function testArrayAccess(): void
    {
        $city = new Node('city');
        $car = new Node('car', ['make' => 'Seat']);
        $cave = new Node('cave');

        // Testing OffsetExists

        $this->assertFalse($city->offsetExists('name'));
        $this->assertFalse($cave->offsetExists('name'));
        $this->assertFalse(isset($city['name']));
        $this->assertFalse(isset($cave['name']));

        $city->attributes['name'] = 'London';

        $cave['name'] = 'Altamira';

        $this->assertTrue($city->offsetExists('name'));
        $this->assertTrue($car->offsetExists('make'));
        $this->assertTrue($cave->offsetExists('name'));
        $this->assertTrue(isset($city['name']));
        $this->assertTrue(isset($car['make']));
        $this->assertTrue(isset($cave['name']));

        // Testing OffsetGet
        $this->assertEquals('London', $city->offsetGet('name'));
        $this->assertEquals('London', $city['name']);

        // Testing OffsetUnset
        $city->offsetUnset('name');
        $this->assertFalse(isset($city['name']));

        $city->offsetUnset('population');
        $this->assertFalse(isset($city['population']));

        unset($car['make']);

        $this->assertFalse(isset($car['make']));

        // Testing OffsetSet
        $cave->offsetSet('altitude', '1000m');
        $this->assertEquals('1000m', $cave['altitude']);

        $cave['altitude'] = '800m';

        $this->assertEquals('800m', $cave['altitude']);
    }

    public function testMissingAttribute(): void
    {
        $node = new Node('fail');

        $this->expectException(\LogicException::class);

        $node['name'];
    }

    public function testIndexByAttribute(): void
    {
        $pizza = new Node('pizza');

        $pizza->addChild($peperoni = new Node('ingredient', ['name' => 'peperoni']));
        $pizza->addChild($cheese = new Node('ingredient', ['name' => 'cheese']));
        $pizza->addChild($slice = new Node('slice'));

        $this->assertCount(2, $pizza->namedChildren['ingredient']);
        $this->assertCount(1, $pizza->namedChildren['slice']);

        $this->assertEquals($slice, $pizza->namedChildren['slice'][0]);
        $this->assertEquals($peperoni, $pizza->namedChildren['ingredient'][0]);
        $this->assertEquals($cheese, $pizza->namedChildren['ingredient'][1]);

        $this->assertArrayNotHasKey('cheese', $pizza->namedChildren['ingredient']);

        $pizza->indexByAttribute('ingredient', 'name');

        $this->assertCount(2, $pizza->namedChildren['ingredient']);
        $this->assertCount(1, $pizza->namedChildren['slice']);

        $this->assertArrayHasKey('peperoni', $pizza->namedChildren['ingredient']);
        $this->assertArrayHasKey('cheese', $pizza->namedChildren['ingredient']);

        $this->assertEquals($peperoni, $pizza->namedChildren['ingredient']['peperoni']);

        $this->assertArrayNotHasKey('sweetcorn', $pizza->namedChildren['ingredient']);
    }

    public function testIndexByAttributeWithNoNodes(): void
    {
        $pizza = new Node('pizza');

        $pizza->indexByAttribute('ingredient', 'name');

        $pizza->addChild($peperoni = new Node('ingredient', ['name' => 'peperoni']));

        $this->assertEquals($peperoni, $pizza->namedChildren['ingredient']['peperoni']);
    }

    public function testIndexByAttributeCollision(): void
    {
        $pizza = new Node('pizza');
        $peperoni = new Node('ingredient', ['name' => 'peperoni']);

        $pizza->addChild($peperoni);
        $pizza->addChild($peperoni);

        $this->expectException(\LogicException::class);

        $pizza->indexByAttribute('ingredient', 'name');
    }

    public function testIndexByAttributeMissingAttribute(): void
    {
        $pizza = new Node('pizza');

        $pizza->addChild(new Node('ingredient', ['name' => 'peperoni']));

        $this->expectException(\InvalidArgumentException::class);

        $pizza->indexByAttribute('ingredient', 'quality');
    }

    public function testIndexByAttributeWithNoNodesAndAddOneMissingAttribute(): void
    {
        $pizza = new Node('pizza');

        $pizza->indexByAttribute('ingredient', 'name');

        $this->expectException(\LogicException::class);

        $pizza->addChild($peperoni = new Node('ingredient', ['quality' => 'high']));
    }

    public function testGetNamedChildren(): void
    {
        $pizza = new Node('pizza');

        $pizza->addChild($peperoni = new Node('ingredient', ['name' => 'peperoni']));
        $pizza->addChild($cheddar = new Node('ingredient', ['name' => 'cheese', 'type' => 'cheddar']));
        $pizza->addChild($gouda = new Node('ingredient', ['name' => 'cheese', 'type' => 'gouda']));
        $pizza->addChild($slice = new Node('slice'));

        $this->assertContains($peperoni, $pizza->getNamedChildren('ingredient'));
        $this->assertNotContains($slice, $pizza->getNamedChildren('ingredient'));
        $this->assertCount(3, $pizza->getNamedChildren('ingredient'));

        $this->assertCount(2, $cheeses = $pizza->getNamedChildren('ingredient', ['name' => 'cheese']));
        $this->assertContains($gouda, $cheeses);

        $this->assertEmpty($pizza->getNamedChildren('eaters'));
        $this->assertEmpty($pizza->getNamedChildren('ingredient', ['name' => 'sweetcorn']));

        $this->assertNotEmpty($pizza->getNamedChildren('ingredient', ['name' => 'sweetcorn'], true));
        $this->assertNotEmpty($pizza->getNamedChildren('eater', [], true));
    }

    public function testGetNamedChild(): void
    {
        $pizza = new Node('pizza');

        $pizza->addChild($peperoni = new Node('ingredient', ['name' => 'peperoni']));
        $pizza->addChild($cheddar = new Node('ingredient', ['name' => 'cheese', 'type' => 'cheddar']));
        $pizza->addChild($gouda = new Node('ingredient', ['name' => 'cheese', 'type' => 'gouda']));
        $pizza->addChild($slice = new Node('slice'));

        $this->assertEquals($slice, $pizza->getNamedChild('slice'));
        $this->assertEquals($peperoni, $pizza->getNamedChild('ingredient', ['name' => 'peperoni']));
        $this->assertInstanceOf(Node::class, $eater = $pizza->getNamedChild('eater', [], true));
        $this->assertEquals('eater', $eater->name);
        $this->assertEmpty($eater->attributes);
        $this->assertEmpty($eater->children);
    }

    public function testGetNamedChildInvalidNumberMatches(): void
    {
        $pizza = new Node('pizza');

        $pizza->addChild($cheddar = new Node('ingredient', ['name' => 'cheese', 'type' => 'cheddar']));
        $pizza->addChild($gouda = new Node('ingredient', ['name' => 'cheese', 'type' => 'gouda']));

        $this->expectException(\LogicException::class);

        $pizza->getNamedChild('ingredient', ['name' => 'cheese']);
    }
}
