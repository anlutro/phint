<?php
namespace Phint\Context;

use PhpParser\Node;

class Variable
{
	protected $node;
	protected $type;

	public function __construct(Node $node, $type)
	{
		$this->node = $node;
		$this->type = $type;
	}

	public function getNode()
	{
		return $this->node;
	}

	public function getType()
	{
		return $this->type;
	}

	protected static $nonClassTypes = ['mixed', 'null', 'void', 'string', 'int',
		'integer', 'float', 'double', 'bool', 'boolean', 'array', 'object'];

	public static function isClassType($type)
	{
		return ! in_array($type, static::$nonClassTypes, true);
	}
}
