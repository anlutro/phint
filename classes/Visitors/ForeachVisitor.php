<?php
namespace Phint\Visitors;

use Phint\AbstractNodeVisitor;
use Phint\NodeVisitorInterface;
use Phint\Context\Variable;
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
		$valueVar = new Variable($node->valueVar, $this->guessValueType($node));
		$ctx->setVariable($node->valueVar->name, $valueVar);

		if ($node->keyVar) {
			$ctx->setVariable($node->keyVar->name, $node->keyVar);
		}

		$this->recurse($node->stmts);
	}

	private function guessValueType(Foreach_ $node)
	{
		if ($node->expr instanceof \PhpParser\Node\Expr\Variable) {
			$var = $this->getContext()
				->getVariable($node->expr->name);
			if ($var) {
				$types = $var->getType();
			}
		} elseif (
			$node->expr instanceof \PhpParser\Node\Expr\MethodCall ||
			$node->expr instanceof \PhpParser\Node\Expr\PropertyFetch
		) {
			$types = $this->traverseVariableChain($node->expr);
		}

		if (isset($types) && $types) {
			if (!is_array($types)) {
				$types = [$types];
			}
			foreach ($types as $type) {
				if (substr($type, -2) == '[]') {
					return substr($type, 0, -2);
				}

				// TODO: iterator classes etc?
			}
		}
	}
}
