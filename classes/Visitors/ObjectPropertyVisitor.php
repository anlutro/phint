<?php
namespace Phint\Visitors;

use Phint\AbstractNodeVisitor;
use Phint\Error;
use Phint\NodeVisitorInterface;
use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use ReflectionClass;

class ObjectPropertyVisitor extends AbstractNodeVisitor implements NodeVisitorInterface
{
	public function visit(Node $node)
	{
		if (! $node instanceof PropertyFetch) {
			return;
		}

		if ($node->var->name == 'this' && ! $node->var->var) {
			// property name is dynamic (variables/string concatenation)
			if (!is_string($node->name)) {
				return;
			}

			$reflClass = $this->getContext()
				->getReflectionClass();
			if (!$reflClass->hasProperty($node->name)) {
				$this->addError($this->createUndefinedPropertyError($node, $reflClass));
			}
		} else {
			// TODO
		}
	}

	private function createUndefinedPropertyError(PropertyFetch $node, ReflectionClass $reflClass)
	{
		$class = $reflClass->getName();
		$prop = $node->name;
		$msg = "Undefined property: $class::\$$prop";
		return new Error($msg, $node);
	}
}
