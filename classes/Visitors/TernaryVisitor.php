<?php
namespace Phint\Visitors;

use Phint\AbstractNodeVisitor;
use Phint\NodeVisitorInterface;
use PhpParser\Node;
use PhpParser\Node\Expr\Ternary;

class TernaryVisitor extends AbstractNodeVisitor implements NodeVisitorInterface
{
	public function visit(Node $node)
	{
		if (! $node instanceof Ternary) {
			return;
		}

		if ($node->if) {
			$this->recurse($node->cond, $node->if, $node->else);
		} else {
			$this->recurse($node->cond, $node->else);
		}
	}
}
