<?php
namespace Phint\Visitors;

use Phint\AbstractNodeVisitor;
use Phint\Error;
use Phint\NodeVisitorInterface;
use PhpParser\Node;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Name;

class InstanceofVisitor extends AbstractNodeVisitor implements NodeVisitorInterface
{
	public function visit(Node $node)
	{
		if (! $node instanceof Instanceof_) {
			return;
		}

		$this->recurse($node->expr);

		if ($node->class instanceof Name) {
			$className = $node->class->toString();
		}

		if (isset($className)) {
			$className = $this->getContext()
				->getClassName($className);
			if (!$this->classExists($className)) {
				$this->addError($this->createClassNotFoundError($className, $node));
			}
		}
	}

	private function classExists($className)
	{
		return class_exists($className) || interface_exists($className);
	}

	private function createClassNotFoundError($className, Instanceof_ $node)
	{
		$className = ltrim($className, '\\');
		$msg = "Testing instanceof against non-existant class: $className";
		return new Error($msg, $node);
	}
}
