<?php
namespace Phint;

use ReflectionFunctionAbstract;
use ReflectionClass;
use Phint\Context\FileContext;
use Phint\Context\FunctionContext;

class ContextWrapper
{
	protected $fileContext;
	protected $funcContext;

	public function __construct(
		FileContext $fileContext = null,
		FunctionContext $funcContext = null
	) {
		$this->fileContext = $fileContext ?: new FileContext();
		$this->funcContext = $funcContext ?: new FunctionContext();
	}

	public function setVariable($name, $value)
	{
		$this->funcContext->setVariable($name, $value);
	}

	public function unsetVariable($name)
	{
		$this->funcContext->unsetVariable($name);
	}

	public function getVariable($name)
	{
		return $this->funcContext->getVariable($name);
	}

	public function resetVariables()
	{
		$this->funcContext->resetVariables();
	}

	public function setReflectionFunction(ReflectionFunctionAbstract $reflFunc)
	{
		$this->funcContext->setReflectionFunction($reflFunc);
	}

	public function getReflectionFunction()
	{
		return $this->funcContext->getReflectionFunction();
	}

	public function setFunctionNode($classNode)
	{
		$this->funcContext->setFunctionNode($classNode);
	}

	public function getFunctionNode()
	{
		return $this->funcContext->getFunctionNode();
	}

	public function setClassNode($classNode)
	{
		$this->fileContext->setClassNode($classNode);
	}

	public function getClassNode()
	{
		return $this->fileContext->getClassNode();
	}

	public function setReflectionClass(ReflectionClass $reflClass)
	{
		$this->fileContext->setReflectionClass($reflClass);
	}

	public function getReflectionClass()
	{
		return $this->fileContext->getReflectionClass();
	}

	public function setNamespace($namespace)
	{
		$this->fileContext->setNamespace($namespace);
	}

	public function getNamespace()
	{
		return $this->fileContext->getNamespace();
	}

	public function import($className, $alias = null)
	{
		$this->fileContext->import($className, $alias);
	}

	public function getClassName($className)
	{
		return $this->fileContext->getClassName($className);
	}
}
