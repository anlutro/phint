<?php
namespace Phint\Visitors;

use Phint\AbstractNodeVisitor;
use Phint\NodeVisitorInterface;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use ReflectionClass;

class ClassVisitor extends AbstractNodeVisitor implements NodeVisitorInterface
{
	public function visit(Node $node)
	{
		if (! $node instanceof Class_) {
			return;
		}

		$ctx = $this->getContext();

		$className = $ctx->getClassName($node->name);
		$ctx->setReflectionClass(new ReflectionClass($className));
		$ctx->setClassNode($node);

		$this->recurse($node->stmts);
	}
}
