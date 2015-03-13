<?php
namespace Phint\Visitors;

use Phint\AbstractNodeVisitor;
use Phint\Error;
use Phint\NodeVisitorInterface;
use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;

class ClassMethodVisitor extends AbstractNodeVisitor implements NodeVisitorInterface
{
	public function visit(Node $node)
	{
		if (! $node instanceof ClassMethod) {
			return;
		}

		$ctx = $this->getContext();

		$ctx->resetVariables();
		$ctx->setFunctionNode($node);
		$reflClass = $ctx->getReflectionClass();
		$ctx->setReflectionFunction($reflClass->getMethod($node->name));

		foreach ($node->params as $param) {
			if ($param->type instanceof Name) {
				$className = $ctx->getClassName($param->type->toString());
				if (!$this->classExists($className)) {
					$this->addError($this->createClassNotFoundError(
						$className, $node, $param
					));
				}
			}
			$ctx->setVariable($param->name, $param);
		}

		$this->recurse($node->stmts);
	}

	private function classExists($className)
	{
		return class_exists($className) || interface_exists($className);
	}

	private function createClassNotFoundError($className, ClassMethod $node,
		Param $param)
	{
		$className = ltrim($className, '\\');
		$method = $node->name.'::'.$param->name;
		$msg = "$method() argument \${$param->name} is type-hinted "
			. "against a non-existant class: $className";
		return new Error($msg, $param);
	}
}
