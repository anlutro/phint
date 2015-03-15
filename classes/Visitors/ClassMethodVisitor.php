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
				$type = $ctx->getClassName($param->type);
				if (!$this->classExists($type)) {
					$this->addError($this->createClassNotFoundError(
						$reflClass->getName(), $node, $param, $type
					));
				}
			}

			$ctx->setVariable($param->name, $param);
		}

		if (!$node->isAbstract()) {
			$this->recurse($node->stmts);
		}

		$ctx->setReflectionFunction(null);
	}

	private function classExists($className)
	{
		return class_exists($className) || interface_exists($className);
	}

	private function createClassNotFoundError($class, ClassMethod $node,
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
