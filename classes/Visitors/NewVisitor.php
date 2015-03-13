<?php
namespace Phint\Visitors;

use Phint\AbstractNodeVisitor;
use Phint\Error;
use Phint\NodeVisitorInterface;
use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;

class NewVisitor extends AbstractNodeVisitor implements NodeVisitorInterface
{
	public function visit(Node $node)
	{
		if (! $node instanceof New_) {
			return;
		}

		if ($node->class instanceof Name) {
			$className = $node->class->toString();
			if ($node->class->isFullyQualified()) {
				$className = '\\'.$className;
			}
		}

		if (isset($className) && $className !== 'static' && $className !== 'self') {
			$className = $this->getContext()
				->getClassName($className);
			if (!class_exists($className)) {
				$this->addError($this->createClassNotFoundError($className, $node));
			}
		}
	}

	private function createClassNotFoundError($className, New_ $node)
	{
		$className = ltrim($className, '\\');
		$msg = "Tried instantiating non-existant class: $className";
		return new Error($msg, $node);
	}
}
