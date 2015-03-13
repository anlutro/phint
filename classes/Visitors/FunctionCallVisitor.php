<?php
namespace Phint\Visitors;

use Phint\AbstractNodeVisitor;
use Phint\Error;
use Phint\NodeVisitorInterface;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;

class FunctionCallVisitor extends AbstractNodeVisitor implements NodeVisitorInterface
{
	public function visit(Node $node)
	{
		if (! $node instanceof FuncCall) {
			return;
		}

		if ($node->name instanceof Name) {
			$func = $node->name->toString();
			$fqfn = $this->getContext()
				->getClassName($func);
			if (!function_exists($func) && !function_exists($fqfn)) {
				$this->addError($this->createUndefinedFunctionError($func, $node));
			}
		}

		$argValues = array_map(function($arg) {
			return $arg->value;
		}, $node->args);
		$this->recurse($argValues);
	}

	private function createUndefinedFunctionError($func, FuncCall $node)
	{
		$msg = "Call to undefined function: $func";
		return new Error($msg, $node);
	}
}
