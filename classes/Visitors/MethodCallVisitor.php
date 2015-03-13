<?php
namespace Phint\Visitors;

use Phint\AbstractNodeVisitor;
use Phint\Error;
use Phint\NodeVisitorInterface;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use ReflectionClass;

class MethodCallVisitor extends AbstractNodeVisitor implements NodeVisitorInterface
{
	public function visit(Node $node)
	{
		if (! $node instanceof MethodCall) {
			return;
		}

		if ($node->var->name == 'this' && ! $node->var->var) {
			$reflClass = $this->getContext()
				->getReflectionClass();
			if (!$reflClass->hasMethod($node->name)) {
				$this->addError($this->createUndefinedMethodError($node, $reflClass));
			}
		} else {
			// TODO
		}
	}

	private function createUndefinedMethodError(MethodCall $node, ReflectionClass $reflClass)
	{
		$class = $reflClass->getName();
		$method = $node->name;
		$msg = "Call to undefined method: $class::$method()";
		return new Error($msg, $node);
	}
}
