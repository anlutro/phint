<?php
namespace Phint\Visitors;

use Phint\AbstractNodeVisitor;
use Phint\Error;
use Phint\NodeVisitorInterface;
use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
use ReflectionClass;

class NewVisitor extends AbstractNodeVisitor implements NodeVisitorInterface
{
	public function visit(Node $node)
	{
		if (! $node instanceof New_) {
			return;
		}

		$className = $this->getContext()->getClassName($node->class);

		if (!$className) {
			return false;
		}

		if (!class_exists($className)) {
			$this->addError($this->createClassNotFoundError($className, $node));
			return false;
		}

		$reflClass = new ReflectionClass($className);

		if ($reflClass->hasMethod('__construct')) {
			$reflMethod = $reflClass->getMethod('__construct');

			if (!$reflMethod->isPublic()) {
				// TODO
			}

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
		} else {
			$requiredParams = 0;
		}

		if (count($node->args) < $requiredParams) {
			$this->addError($this->createNotEnoughParamsError($node,
				$reflClass, $requiredParams));
		}

		$argValues = array_map(function($arg) {
			return $arg->value;
		}, $node->args);
		$this->recurse($argValues);
	}

	private function createClassNotFoundError($className, New_ $node)
	{
		$className = ltrim($className, '\\');
		$msg = "Tried instantiating non-existant class: $className";
		return new Error($msg, $node);
	}

	private function createNotEnoughParamsError(New_ $node, ReflectionClass $reflClass, $requiredParams)
	{
		$numArgs = count($node->args);
		$msg = "Method {$reflClass->getName()}::__construct() requires $requiredParams arguments, $numArgs given";
		return new Error($msg, $node);
	}
}
