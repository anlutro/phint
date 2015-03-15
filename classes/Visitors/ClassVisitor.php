<?php
namespace Phint\Visitors;

use Phint\AbstractNodeVisitor;
use Phint\PhintException;
use Phint\NodeVisitorInterface;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use ReflectionClass;
use ReflectionException;

class ClassVisitor extends AbstractNodeVisitor implements NodeVisitorInterface
{
	public function visit(Node $node)
	{
		if (! $node instanceof Class_) {
			return;
		}

		$ctx = $this->getContext();

		$className = $ctx->getClassName($node->name);
		try {
			$refl = new ReflectionClass($className);
		} catch (ReflectionException $e) {
			$className = ltrim($className, '\\');
			$msg = "Class $className could not be autoloaded";
			throw new PhintException($msg, $e->getCode(), $e);
		}
		$ctx->setReflectionClass($refl);
		$ctx->setClassNode($node);
		$ctx->setVariable('this', $node);

		$this->recurse($node->stmts);

		$ctx->setReflectionClass(null);
	}
}
