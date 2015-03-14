<?php
namespace Phint\Visitors;

use Phint\AbstractNodeVisitor;
use Phint\Error;
use Phint\NodeVisitorInterface;
use Phint\Context\FunctionContext;
use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\ClosureUse;

class ClosureVisitor extends AbstractNodeVisitor implements NodeVisitorInterface
{
	public function visit(Node $node)
	{
		if (! $node instanceof Closure) {
			return;
		}

		$ctx = $this->getContext();
		$newContext = new FunctionContext;

		foreach ($node->uses as $use) {
			if ($use->byRef) {
				$ctx->setVariable($use->var, $use);
			} else {
				if (!$ctx->getVariable($use->var)) {
					$this->addError($this->createUndefinedVariableError($use));
				}
			}
			$newContext->setVariable($use->var, $use);
		}

		foreach ($node->params as $param) {
			$newContext->setVariable($param->name, $param);
		}

		if (PHP_VERSION_ID >= 50400) {
			$newContext->setVariable('this', $ctx->getVariable('this'));
		}

		$this->recurseWithNewContext($newContext, $node->stmts);
	}

	private function createUndefinedVariableError(ClosureUse $node)
	{
		$msg = "Undefined variable: \${$node->var}";
		return new Error($msg, $node);
	}
}
