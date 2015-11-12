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

	protected static $nonClassTypes = ['mixed', 'void',
		'null', 'string', 'int', 'integer', 'float', 'double',
		'array', 'object', 'resource', 'callable',
		'bool', 'boolean', 'true', 'false'];

	/**
	 * Determine if a type is a class type.
	 *
	 * @param  string  $type
	 *
	 * @return boolean
	 */
	public static function isClassType($type)
	{
		return ! in_array($type, static::$nonClassTypes, true);
	}
}
