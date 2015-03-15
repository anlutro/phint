<?php
namespace Phint\Visitors;

use Phint\AbstractNodeVisitor;
use Phint\Error;
use Phint\NodeVisitorInterface;
use ReflectionClass;
use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\AssignRef;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\List_;

class AssignVisitor extends AbstractNodeVisitor implements NodeVisitorInterface
{
	public function visit(Node $node)
	{
		if (! $node instanceof Assign && ! $node instanceof AssignRef) {
			return;
		}

		$ctx = $this->getContext();

		if ($node->var instanceof PropertyFetch) {
			$this->traverseVariableChain($node->var);
		}

		if ($node->var instanceof List_) {
			foreach ($node->var->vars as $var) {
				if ($var instanceof Variable) {
					$ctx->setVariable($var->name, $node->expr);
				} elseif ($node->var instanceof PropertyFetch) {
					$this->traverseVariableChain($node->var);
				}
			}
		}

		if (
			$node->expr instanceof MethodCall ||
			$node->expr instanceof StaticCall ||
			$node->expr instanceof PropertyFetch
		) {
			$type = $this->traverseVariableChain($node->expr);
			$var = new \Phint\Context\Variable($node->expr, $type);
		} else {
			$this->recurse($node->expr);
			$var = $node->expr;
		}

		if ($node->var instanceof Variable) {
			$ctx->setVariable($node->var->name, $var);
		}

	}

	private function createUndefinedPropertyError(PropertyFetch $node, ReflectionClass $reflClass)
	{
		$class = $reflClass->getName();
		$prop = $node->name;
		$msg = "Undefined property: $class::\$$prop";
		return new Error($msg, $node);
	}
}
