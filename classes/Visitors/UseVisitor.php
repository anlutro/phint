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
			$ctx->import($use->name->toString(), $use->alias);
		}
	}
}
