<?php
namespace Phint\Visitors;

use Phint\AbstractNodeVisitor;
use Phint\NodeVisitorInterface;
use PhpParser\Node;
use PhpParser\Node\Stmt\TryCatch;

class TryCatchVisitor extends AbstractNodeVisitor implements NodeVisitorInterface
{
	public function visit(Node $node)
	{
		if (! $node instanceof TryCatch) {
			return;
		}

		$this->recurse($node->stmts, $node->catches);

		if ($node->finallyStmts) {
			$this->recurse($node->finallyStmts);
		}
	}
}
