<?php
namespace Phint\Visitors;

use Phint\AbstractNodeVisitor;
use Phint\NodeVisitorInterface;
use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp;

class BinaryOpVisitor extends AbstractNodeVisitor implements NodeVisitorInterface
{
	public function visit(Node $node)
	{
		if (! $node instanceof BinaryOp) {
			return;
		}

		$this->recurse($node->left, $node->right);
	}
}
