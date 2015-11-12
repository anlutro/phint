<?php
namespace Phint\Visitors;

use Phint\AbstractNodeVisitor;
use Phint\DocblockParser;
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
use PhpParser\Node\Expr\New_;

class AssignVisitor extends AbstractNodeVisitor implements NodeVisitorInterface
{
	public function visit(Node $node)
	{
		if (! $node instanceof Assign && ! $node instanceof AssignRef) {
			return;
		}

		$this->innerCheck($node);
	}

	/**
	 * Really check the node.
	 *
	 * @param  Assign|AssignRef $node
	 *
	 * @return void
	 */
	private function innerCheck(Node $node)
	{
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
			$type = $this->getType($node);
			$var = new \Phint\Context\Variable($node->expr, $type);
			$this->recurse($node->expr);
		}

		if ($node->var instanceof Variable) {
			$ctx->setVariable($node->var->name, $var);
		}
	}

	private function getType($node)
	{
		$type = null;

		if ($node->hasAttribute('comments')) {
			foreach ($node->getAttribute('comments') as $comment) {
				$type = DocblockParser::getVariableType($comment->getText());
			}
		}

		if ($type) return $type;

		if ($node->expr instanceof New_) {
			$className = $this->getContext()->getClassName($node->expr->class);

			if ($className && !class_exists($className)) {
				$this->addError($this->createClassNotFoundError($className, $node->expr));
				return false;
			}

			return new ReflectionClass($className);
		}
	}

	private function createClassNotFoundError($className, New_ $node)
	{
		$className = ltrim($className, '\\');
		$msg = "Tried instantiating non-existant class: $className";
		return new Error($msg, $node);
	}

	private function createUndefinedPropertyError(PropertyFetch $node, ReflectionClass $reflClass)
	{
		$class = $reflClass->getName();
		$prop = $node->name;
		$msg = "Undefined property: $class::\$$prop";
		return new Error($msg, $node);
	}
}
