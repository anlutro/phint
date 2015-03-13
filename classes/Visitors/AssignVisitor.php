<?php
namespace Phint\Visitors;

use Phint\AbstractNodeVisitor;
use Phint\NodeVisitorInterface;
use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\List_;

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

		$ctx = $this->getContext();

		if ($node->var instanceof List_) {
			foreach ($node->var->vars as $var) {
				if ($var instanceof Variable) {
					$ctx->setVariable($var->name, $node->expr);
				}
			}
		}

		$this->recurse($node->expr);

		if ($node->var instanceof Variable) {
			$ctx->setVariable($node->var->name, $node->expr);
		}
	}
}
