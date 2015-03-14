<?php
namespace Phint\Visitors;

use Phint\AbstractNodeVisitor;
use Phint\NodeVisitorInterface;
use PhpParser\Node;
use PhpParser\Node\Stmt\Switch_;

class SwitchVisitor extends AbstractNodeVisitor implements NodeVisitorInterface
{
	public function visit(Node $node)
	{
		if (! $node instanceof Switch_) {
			return;
		}

		$subnodes = [$node->cond];

		foreach ($node->cases as $case) {
			if ($case->cond) {
				$subnodes[] = $case->cond;
			}
			foreach ($case->stmts as $stmt) {
				$subnodes[] = $stmt;
			}
		}

		$this->recurse($subnodes);
	}
}
