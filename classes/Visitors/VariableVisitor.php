<?php
namespace Phint\Visitors;

use Phint\AbstractNodeVisitor;
use Phint\Error;
use Phint\NodeVisitorInterface;
use PhpParser\Node;
use PhpParser\Node\Expr\Variable;

class VariableVisitor extends AbstractNodeVisitor implements NodeVisitorInterface
{
	public function visit(Node $node)
	{
		if (! $node instanceof Variable) {
			return;
		}

		$ctx = $this->getContext();
		if (!$ctx->getVariable($node->name)) {
			$this->addError($this->createError($node));
		}
	}

	private function createError(Variable $node)
	{
		$msg = "Undefined variable: \${$node->name}";
		return new Error($msg, $node);
	}
}
