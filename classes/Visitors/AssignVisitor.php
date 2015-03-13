<?php
namespace Phint\Visitors;

use Phint\AbstractNodeVisitor;
use Phint\NodeVisitorInterface;
use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Expr\PropertyFetch;

class AssignVisitor extends AbstractNodeVisitor implements NodeVisitorInterface
{
	public function visit(Node $node)
	{
		if (! $node instanceof Assign) {
			return;
		}

		if ($node->var instanceof PropertyFetch) {
			$this->recurse($node->var);
		}

		$this->recurse($node->expr);

		if ($node->var instanceof Variable) {
			$this->getContext()
				->setVariable($node->var->name, $node->expr);
		}
	}
}
