<?php
namespace Phint\Visitors;

use Phint\AbstractNodeVisitor;
use Phint\NodeVisitorInterface;
use PhpParser\Node;
use PhpParser\Node\Stmt\Foreach_;

class ForeachVisitor extends AbstractNodeVisitor implements NodeVisitorInterface
{
	public function visit(Node $node)
	{
		if (! $node instanceof Foreach_) {
			return;
		}

		$this->recurse($node->expr);

		$ctx = $this->getContext();
		$ctx->setVariable($node->valueVar->name, $node->valueVar);
		if ($node->keyVar) {
			$ctx->setVariable($node->keyVar->name, $node->keyVar);
		}

		$this->recurse($node->stmts);
	}
}
