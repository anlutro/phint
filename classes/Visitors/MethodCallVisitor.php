<?php
namespace Phint\Visitors;

use Phint\AbstractNodeVisitor;
use Phint\Error;
use Phint\NodeVisitorInterface;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use ReflectionClass;
use ReflectionMethod;

class MethodCallVisitor extends AbstractNodeVisitor implements NodeVisitorInterface
{
	public function visit(Node $node)
	{
		if (! $node instanceof MethodCall) {
			return;
		}

		$reflClass = $this->getReflectionClass($node->var);

		if (isset($reflClass)) {
			$reflMethod = $this->getReflectionMethod($reflClass, $node);
		}

		if (isset($reflMethod)) {
			$this->checkParams($reflMethod, $node);
		}

		// recurse over the values of each argument
		$this->recurse(array_map(function($arg) {
			return $arg->value;
		}, $node->args));

		return true;
	}

	private function getReflectionClass(Expr $varNode)
	{
		if ($varNode instanceof New_) {
			return; // TODO
		}

		if ($varNode->name == 'this' && ! isset($varNode->var)) {
			return $this->getContext()
				->getReflectionClass();
		}

		// TODO
	}

	private function getReflectionMethod(ReflectionClass $reflClass, MethodCall $node)
	{
		if (!is_string($node->name)) {
			// method name is dynamic (variables/string concatenation)
			// TODO
		} elseif (
			$reflClass->hasMethod($node->name) ||
			$reflClass->hasMethod('__call')
		) {
			$reflMethod = $reflClass->getMethod($node->name);
		} else {
			$this->addError($this->createUndefinedMethodError(
				$node, $reflClass));
		}
	}

	private function checkParams(ReflectionMethod $reflMethod, MethodCall $node)
	{
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
