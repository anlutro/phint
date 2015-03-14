<?php
namespace Phint\Visitors;

use Phint\AbstractNodeVisitor;
use Phint\Error;
use Phint\NodeVisitorInterface;
use PhpParser\Node;
use PhpParser\Node\Expr\Variable;

class VariableVisitor extends AbstractNodeVisitor implements NodeVisitorInterface
{
	protected static $globals = [
		'GLOBALS', '_SESSION', '_SERVER', '_ENV', '_GET', '_POST', '_REQUEST',
	];

	public function visit(Node $node)
	{
		if (! $node instanceof Variable) {
			return;
		}

		$ctx = $this->getContext();
		if (
			! $ctx->getVariable($node->name) &&
			! in_array($node->name, static::$globals, true)
		) {
			$this->addError($this->createError($node));
			return false;
		}

		return true;
	}

	private function createError(Variable $node)
	{
		$msg = "Undefined variable: \${$node->name}";
		return new Error($msg, $node);
	}
}
