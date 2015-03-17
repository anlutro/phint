<?php
namespace Phint\Visitors;

use Phint\AbstractNodeVisitor;
use Phint\NodeVisitorInterface;
use PhpParser\Node;
use PhpParser\Node\Stmt\Function_;
use ReflectionFunction;

class FunctionVisitor extends AbstractNodeVisitor implements NodeVisitorInterface
{
	public function visit(Node $node)
	{
		if (! $node instanceof Function_) {
			return;
		}

		$ctx = $this->getContext();

		$ctx->resetVariables(false);
		$ctx->setFunctionNode($node);
		$ctx->setReflectionFunction(new ReflectionFunction($node->name));

		foreach ($node->params as $param) {
			if ($param->type instanceof Name) {
				$type = $ctx->getClassName($param->type);
				if (!$this->classExists($type)) {
					$this->addError($this->createClassNotFoundError(
						$reflClass->getName(), $node, $param, $type
					));
				}
			}

			$ctx->setVariable($param->name, $param);
		}

		$this->recurse($node->stmts);

		$ctx->setReflectionFunction(null);
	}

	private function createClassNotFoundError($class, Function_ $node,
		Param $param, $type)
	{
		$class = ltrim($class, '\\');
		$type = ltrim($type, '\\');
		$method = $class.'::'.$node->name;
		$msg = "$method() argument \${$param->name} is type-hinted "
			. "against a non-existant class: $type";
		return new Error($msg, $param);
	}
}
