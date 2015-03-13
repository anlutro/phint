<?php
namespace Phint\Context;

use ReflectionFunctionAbstract;
use PhpParser\Node\Stmt;

class FunctionContext
{
	protected $variables = [];
	protected $reflectionFunction;
	protected $functionNode;

	public function setReflectionFunction(ReflectionFunctionAbstract $reflectionFunction)
	{
		$this->reflectionFunction = $reflectionFunction;
	}

	public function getReflectionFunction()
	{
		return $this->reflectionFunction;
	}

	/**
	 * Get the function node.
	 *
	 * @return Stmt\Function_|Stmt\ClassMethod
	 */
	public function getFunctionNode()
	{
		return $this->functionNode;
	}
	
	/**
	 * Set the function node.
	 *
	 * @param $functionNode Stmt\Function_|Stmt\ClassMethod
	 */
	public function setFunctionNode(Stmt $functionNode)
	{
		$this->functionNode = $functionNode;
	}

	public function setVariable($name, $value)
	{
		$this->variables[$name] = $value;
	}

	public function unsetVariable($name)
	{
		unset($this->variables[$name]);
	}

	public function getVariable($name)
	{
		return isset($this->variables[$name]) ? $this->variables[$name] : null;
	}

	public function resetVariables()
	{
		if (isset($this->variables['this'])) {
			$thisVar = $this->variables['this'];
			$this->variables = ['this' => $thisVar];
		} else {
			$this->variables = [];
		}
	}
}
