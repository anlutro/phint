<?php
namespace Phint\Visitors;

use Phint\AbstractNodeVisitor;
use Phint\NodeVisitorInterface;
use PhpParser\Node;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Name;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Expr\Instanceof_;

class IfVisitor extends AbstractNodeVisitor implements NodeVisitorInterface
{
	public function visit(Node $node)
	{
		if (! $node instanceof If_) {
			return;
		}

		$this->visitIf($node);

		foreach ($node->elseifs as $elseif) {
			$this->visitIf($elseif);
		}

		if ($node->else) {
			$this->recurse($node->else->stmts);
		}
	}

	public function visitIf(Node $node)
	{
		$this->recurse($node->cond);
		$instanceofs = $this->findInstanceofs($node->cond);
		$oldVars = [];
		$newVars = [];

		if ($instanceofs) {
			$ctx = $this->getContext();
			foreach ($instanceofs as $instanceof) {
				if (
					$instanceof->expr instanceof Variable &&
					$instanceof->class instanceof Name
				) {
					$oldVar = $ctx->getVariable($instanceof->expr->name);
					$oldVars[$instanceof->expr->name] = $oldVar;
					$newVar = new \Phint\Context\Variable($oldVar->getNode(),
						$ctx->getClassName($instanceof->class));
					$newVars[$instanceof->expr->name] = $newVar;
					$ctx->setVariable($instanceof->expr->name, $newVar);
				}
			}
		}

		$this->recurse($node->stmts);

		foreach ($oldVars as $name => $var) {
			// the variable may have changed inside the if block. if so, leave
			// it alone. whether this is correct behaviour probably depends on a
			// lot of factors.
			if ($ctx->getVariable($name) === $newVars[$name]) {
				$ctx->setVariable($name, $var);
			}
		}
	}

	public function findInstanceofs($cond)
	{
		$instanceofs = [];

		if ($cond instanceof Instanceof_) {
			$instanceofs[] = $cond;
		}

		return $instanceofs;
	}
}
