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

		if ($instanceofs) {
			$ctx = $this->getContext();
			foreach ($instanceofs as $instanceof) {
				if (
					$instanceof->expr instanceof Variable &&
					$instanceof->class instanceof Name
				) {
					$var = $ctx->getVariable($instanceof->expr->name);
					$var = new \Phint\Context\Variable($var->getNode(),
						$ctx->getClassName($instanceof->class));
					$ctx->setVariable($instanceof->expr->name, $var);
				}
			}
		}

		$this->recurse($node->stmts);
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
