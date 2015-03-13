<?php
namespace Phint\Visitors;

use Phint\AbstractNodeVisitor;
use Phint\NodeVisitorInterface;
use PhpParser\Node;
use PhpParser\Node\Stmt\While_;
use PhpParser\Node\Stmt\Do_;

class WhileVisitor extends AbstractNodeVisitor implements NodeVisitorInterface
{
	public function visit(Node $node)
	{
		if ($node instanceof While_) {
			$this->recurse($node->cond, $node->stmts);
		}
		if ($node instanceof Do_) {
			$this->recurse($node->stmts, $node->cond);
		}
	}
}
