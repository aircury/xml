[![Build status][1]][2]

# XML

XML manipulation with PHP made easy

## Installation

### Download the library

This library makes use of Composer. You need to have it on your machine. See [Composer][3] for instructions. Here we 
are assuming that you have Composer installed globally. 

```bash
$ composer require aircury/xml
```

## Documentation

### Class documentation

#### Xml
  * `parseString(string $xmlString): Node` Given an xml string, parses that string into a Node object.  
  * `parseFile(string $path): Node` Given a path, parses that file into a Node object.  
  * `dump(Node $node): string` Giving any Node, will dump it into an xml string.

#### Node

Node is the base class.

If you want to set some attributes, there are three options:   
  1. You can pass them to the constructor. E.g. `new Node('pizza', ['crust' => 'classic'])`   
  2. Node implements \ArrayAccess, so you can use `$pizza['crust']` for setting or reading the attributes.   
  3. Node has a public attribute called `attributes`, so you can access them directly.
     `$pizza->attributes['crust'] = 'classic';`

#### NodeCollection

It is a collection of nodes.

##### Methods
  * `indexByAttribute(string $attribute): NodeCollection` Given an attribute it will index the collection by it
  * `getNamedChildren(string $childName, array $attributes = [], bool $createIfMissing = false): NodeCollection` Get a 
     subset of the child nodes, filtered by `$attributes`. If `$createIfMissing` is passed, it will ensure it is created
     if there are no matches.
  * `getNamedChild(string $childName, array $attributes = [], bool $createIfMissing = true): Node` Same as 
    `getNamedChildren` but expecting only one match.

### Example

```php
$pizza = new Node('pizza'); // Create a new 'pizza' node.

$peperoni = new Node('ingredient', ['name' => 'peperoni', 'spicy' => 'true']);
$slice = new Node('slice');

$pizza->addChild($peperoni);
$pizza->addChild(new Node('ingredient', ['name' => 'cheese', 'type' => 'cheddar']));
$pizza->addChild(new Node('ingredient', ['name' => 'cheese', 'type' => 'camembert']));
$pizza->addChild($slice);

$pizza['crust'] = 'classic'; // Set the crust attribute of the pizza node

unset($peperoni['spicy']); // Remove an attirbute from the peperoni ingredient node

if (isset($peperoni['spicy'])) {
    // ... 
}

// Get all the 'ingredient' nodes of the 'pizza'
$ingredients = $pizza->namedChildren['ingredient'];

// Access the peperoni ingredient
$pizza->namedChildren['ingredient'][0];

// If you want to access them by any of the attributes, you can index them by that attribute
$pizza->indexByAttribute('ingredient', 'name');
$pizza->namedChildren['ingredient']['peperoni'];

// If you want to filter the child nodes by an attribute, you can use `getNamedChildren`
$cheeses = $pizza->getNamedChildren('ingredient', ['name' => 'cheese']); // Will return a NodeCollection with two elements

// By default, if the children that you are after don't exist, it will create them. if you don't want them to be created
// pass the third argument as false
$emptyCollection = $pizza->getNamedChildren('ingredient', ['name' => 'mushroom'], false);

// If there is only one child, you can also use `getNamedChild`
$slice = $pizza->getNamedChild('slice');
```

License
-------
This software is published under the [MIT license](LICENSE).

[1]: https://circleci.com/gh/aircury/xml.svg?style=shield&circle-token=:circle-token
[2]: https://circleci.com/gh/aircury/xml
[3]: https://getcomposer.org/download/
