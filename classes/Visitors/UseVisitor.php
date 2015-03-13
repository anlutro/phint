<?php
namespace Phint\Visitors;

use Phint\AbstractNodeVisitor;
use Phint\Error;
use Phint\NodeVisitorInterface;
use PhpParser\Node;
use PhpParser\Node\Stmt\Use_;

class UseVisitor extends AbstractNodeVisitor implements NodeVisitorInterface
{
	public function visit(Node $node)
	{
		if (! $node instanceof Use_) {
			return;
		}

		$ctx = $this->getContext();

		foreach ($node->uses as $use) {
			$className = $use->name->toString();
			if (!$this->classExists($className)) {
				$this->addError($this->createClassNotFoundError($className, $node));
			}
			$ctx->import($use->name->toString(), $use->alias);
		}
	}

	private function classExists($className)
	{
		if (PHP_VERSION_ID > 54000) {
			return class_exists($className)
				|| interface_exists($className)
				|| trait_exists($className);
		} else {
			return class_exists($className)
				|| interface_exists($className);
		}
	}

	private function createClassNotFoundError($className, Use_ $node)
	{
		$className = ltrim($className, '\\');
		$msg = "Importing non-existant class: $className";
		return new Error($msg, $node);
	}
}
