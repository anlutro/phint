<?php
namespace Phint;

use ReflectionFunctionAbstract;
use ReflectionClass;
use Phint\Context\FileContext;
use Phint\Context\FunctionContext;
use Phint\Context\Variable;
use PhpParser\Node;

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

	public function getFunctionContext()
	{
		return $this->funcContext;
	}

	public function setFunctionContext(FunctionContext $funcContext)
	{
		$this->funcContext = $funcContext;
	}

	public function setVariable($name, $value)
	{
		if (! $value instanceof Variable) {
			$value = $this->createVariable($value);
		}

		$this->funcContext->setVariable($name, $value);
	}

	public function createVariable(Node $node)
	{
		return new Variable($node, $this->guessType($node));
	}

	private function guessType(Node $node)
	{
		// used for $this
		if ($node instanceof \PhpParser\Node\Stmt\Class_) {
			$className = $this->getNamespace().'\\'.$node->name;
			$className = ltrim($className, '\\');
			return $className;
		}

		if ($node instanceof \PhpParser\Node\Param) {
			if ($type = $node->type) {
				if ($type instanceof \PhpParser\Node\Name) {
					$type = $this->getClassName($type);
				}
				return $type;
			}

			$reflFunc = $this->getReflectionFunction();
			if ($reflFunc) {
				$docblock = $reflFunc->getDocComment();
			}

			if (isset($docblock) && $docblock) {
				$type = DocblockParser::getParamType($docblock, $node->name);
				if (Variable::isClassType($type)) {
					$type = $this->getClassName($type);
				}
				return $type;
			}

			return null;
		}

		if ($node instanceof \PhpParser\Node\Expr\New_) {
			if ($node instanceof \PhpParser\Node\Name) {
				return $this->getClassName($node->class);
			} else {
				return 'object';
			}
		}

		if (
			$node instanceof \PhpParser\Node\Scalar\String ||
			$node instanceof \PhpParser\Node\Scalar\Encapsed
		) {
			return 'string';
		}
		if ($node instanceof \PhpParser\Node\Scalar\DNumber) {
			return 'float';
		}
		if ($node instanceof \PhpParser\Node\Scalar\LNumber) {
			return 'integer';
		}
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
