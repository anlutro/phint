<?php
namespace Phint\Visitors;

use Phint\AbstractNodeVisitor;
use Phint\NodeVisitorInterface;
use PhpParser\Node;
use PhpParser\Node\Stmt\Return_;

class ReturnVisitor extends AbstractNodeVisitor implements NodeVisitorInterface
{
	public function visit(Node $node)
	{
		if (! $node instanceof Return_) {
			return;
		}

		// if the code is merely `return;`, $node->expr is null
		if ($node->expr) {
			$this->recurse($node->expr);
		}
	}
}
