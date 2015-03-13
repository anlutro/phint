<?php
namespace Phint\Visitors;

use Phint\AbstractNodeVisitor;
use Phint\NodeVisitorInterface;
use PhpParser\Node;
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
			$ctx->setVariable($param->name, $param);
		}

		$this->recurse($node->stmts);
	}
}
