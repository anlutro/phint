<?php
namespace Phint\Visitors;

use Phint\AbstractNodeVisitor;
use Phint\NodeVisitorInterface;
use PhpParser\Node;
use PhpParser\Node\Stmt\Static_;
use PhpParser\Node\Stmt\StaticVar;

class StaticVarVisitor extends AbstractNodeVisitor implements NodeVisitorInterface
{
	public function visit(Node $node)
	{
		if (! $node instanceof Static_) {
			return;
		}

		$ctx = $this->getContext();

		foreach ($node->vars as $var) {
			$ctx->setVariable($var->name, $var->default);
		}
	}
}
