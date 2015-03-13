<?php
namespace Phint\Visitors;

use Phint\AbstractNodeVisitor;
use Phint\NodeVisitorInterface;
use PhpParser\Node;
use PhpParser\Node\Stmt\If_;

class IfVisitor extends AbstractNodeVisitor implements NodeVisitorInterface
{
	public function visit(Node $node)
	{
		if (! $node instanceof If_) {
			return;
		}

		$this->recurse($node->cond, $node->stmts);

		foreach ($node->elseifs as $elseif) {
			$this->recurse($elseif->cond, $elseif->stmts);
		}

		if ($node->else) {
			$this->recurse($node->else->stmts);
		}
	}
}
