<?php
namespace Phint\Visitors;

use Phint\AbstractNodeVisitor;
use Phint\Error;
use Phint\NodeVisitorInterface;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use ReflectionFunction;
use ReflectionParameter;

class FunctionCallVisitor extends AbstractNodeVisitor implements NodeVisitorInterface
{
	public function visit(Node $node)
	{
		if (! $node instanceof FuncCall) {
			return;
		}

		// $node->name can also be an expression, in which case the function
		// name is dynamic and we can't check anything
		if ($node->name instanceof Name) {
			$this->checkFunction($node->name, $node);
		}

		$argValues = array_map(function($arg) {
			return $arg->value;
		}, $node->args);
		$this->recurse($argValues);
	}

	private function checkFunction(Name $name, FuncCall $node)
	{
		$func = $this->getContext()
			->getClassName($name->toString());

		if (!function_exists($func)) {
			$func = $name->toString();
		}

		if (!function_exists($func)) {
			$this->addError($this->createUndefinedFunctionError($func, $node));
			return;
		}

		$refl = new ReflectionFunction($func);
		$params = $refl->getParameters();

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
			$this->addError($this->createNotEnoughParamsError($node, $func, $requiredParams));
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

	private function createUndefinedFunctionError($func, FuncCall $node)
	{
		$msg = "Call to undefined function: $func";
		return new Error($msg, $node);
	}

	private function createNotEnoughParamsError(FuncCall $node, $func, $requiredParams)
	{
		$numArgs = count($node->args);
		$msg = "Function $func requires $requiredParams arguments, $numArgs given";
		return new Error($msg, $node);
	}
}
