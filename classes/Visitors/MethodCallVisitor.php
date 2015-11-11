<?php
namespace Phint\Visitors;

use Phint\AbstractNodeVisitor;
use Phint\Error;
use Phint\NodeVisitorInterface;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use ReflectionClass;

class MethodCallVisitor extends AbstractNodeVisitor implements NodeVisitorInterface
{
	public function visit(Node $node)
	{
		if (! $node instanceof MethodCall) {
			return;
		}

		if ($node->var->name == 'this' && ! isset($node->var->var)) {
			// method name is dynamic (variables/string concatenation)
			if (!is_string($node->name)) {
				return;
			}

			$reflClass = $this->getContext()
				->getReflectionClass();
			if (
				! $reflClass->hasMethod($node->name) &&
				! $reflClass->hasMethod('__call')
			) {
				$this->addError($this->createUndefinedMethodError(
					$node, $reflClass));
				return false;
			}

			$reflMethod = $reflClass->getMethod($node->name);

			$params = $reflMethod->getParameters();

			// verify number of arguments
			if (count($node->args) > count($params)) {
				// cannot error on this as php functions can use func_get_args()
			}

			$requiredParams = 0;
			foreach ($params as $param) {
				if ($param->isOptional() || $param->isDefaultValueAvailable()) {
					break;
				}
				$requiredParams++;
			}
			if (count($node->args) < $requiredParams) {
				$this->addError($this->createNotEnoughParamsError($node,
					$reflClass, $requiredParams));
			}

			// look for function parameters passed by reference
			foreach ($params as $param) {
				if ($param->isPassedByReference()) {
					$pos = $param->getPosition();
					if (isset($node->args[$pos])) {
						$var = $node->args[$pos]->value;
						if ($var instanceof Variable) {
							$this->getContext()
								->setVariable($var->name, $var);
						}
					}
				}
			}
		} else {
			// TODO
		}

		$argValues = array_map(function($arg) {
			return $arg->value;
		}, $node->args);
		$this->recurse($argValues);

		return true;
	}

	private function createUndefinedMethodError(MethodCall $node, ReflectionClass $reflClass)
	{
		$class = $reflClass->getName();
		$method = $node->name;
		$msg = "Call to undefined method: $class::$method()";
		return new Error($msg, $node);
	}

	private function createNotEnoughParamsError(MethodCall $node, ReflectionClass $reflClass, $requiredParams)
	{
		$numArgs = count($node->args);
		$msg = "Method {$reflClass->getName()}::{$node->name}() requires $requiredParams arguments, $numArgs given";
		return new Error($msg, $node);
	}
}
