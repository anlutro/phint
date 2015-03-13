<?php
namespace Phint\Visitors;

use Phint\AbstractNodeVisitor;
use Phint\NodeVisitorInterface;
use PhpParser\Node;
use PhpParser\Node\Stmt\Namespace_;

class NamespaceVisitor extends AbstractNodeVisitor implements NodeVisitorInterface
{
	public function visit(Node $node)
	{
		if (! $node instanceof Namespace_) {
			return;
		}

		if ($node->name) {
			$namespace = $node->name->toString();
		} else {
			$namespace = null;
		}

		$this->getContext()
			->setNamespace($namespace);

		$this->recurse($node->stmts);
	}
}
