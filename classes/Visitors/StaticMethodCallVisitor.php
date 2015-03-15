<?php
namespace Phint\Visitors;

use Phint\AbstractNodeVisitor;
use Phint\Error;
use Phint\NodeVisitorInterface;
use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;

class StaticMethodCallVisitor extends AbstractNodeVisitor implements NodeVisitorInterface
{
	public function visit(Node $node)
	{
		if (! $node instanceof StaticCall) {
			return;
		}

		$className = $this->getContext()
			->getClassName($node->class);

		if ($className && !class_exists($className)) {
			$this->addClassNotFoundError($className, $node);
		}
	}

	private function addClassNotFoundError($className, StaticCall $node)
	{
		$className = ltrim($className, '\\');
		$msg = "Tried calling static method on non-existant class: $className";
		$this->addError(new Error($msg, $node));
	}
}
