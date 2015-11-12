<?php
namespace Phint;

use ReflectionFunctionAbstract;
use ReflectionClass;
use Phint\Context\FileContext;
use Phint\Context\FunctionContext;
use Phint\Context\Variable;
use Phint\Error;
use Phint\ErrorBag;
use PhpParser\Node;

class ContextWrapper
{
	/** @var FileContext */
	protected $fileContext;

	/** @var FunctionContext */
	protected $funcContext;

	/** @var ErrorBag */
	private $errors;

	public function __construct(
		FileContext $fileContext = null,
		FunctionContext $funcContext = null,
		ErrorBag $errors = null
	) {
		$this->fileContext = $fileContext ?: new FileContext();
		$this->funcContext = $funcContext ?: new FunctionContext();
		$this->errors = $errors;
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
			$docblockType = $hintType = null;

			$reflFunc = $this->getReflectionFunction();
			if ($reflFunc) {
				$docblock = $reflFunc->getDocComment();
			}

			if (isset($docblock) && $docblock) {
				$docblockType = DocblockParser::getParamType($docblock, $node->name);
				if ($docblockType) {
					$docblockType = $this->parseDocblockType($docblockType);
				}
			}

			if ($node->type instanceof \PhpParser\Node\Name) {
				$hintType = $this->getClassName($node->type);
			}

			if ($hintType && $docblockType && $hintType != $docblockType) {
				$msg = "@param docblock and type-hint mismatch for argument \${$node->name}";
				$this->addError(new Error($msg, $node));
			}

			return $hintType ?: $docblockType;
		}

		if ($node instanceof \PhpParser\Node\Expr\New_) {
			if ($node->class instanceof \PhpParser\Node\Name) {
				return $this->getClassName($node->class);
			} else {
				return 'object';
			}
		}

		if ($node instanceof \PhpParser\Node\Expr\Variable) {
			$var = $this->getVariable($node->name);
			return $var ? $var->getType() : null;
		}

		if (
			$node instanceof \PhpParser\Node\Scalar\String_ ||
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

	public function parseDocblockType($type)
	{
		return $this->fileContext->parseDocblockType($type);
	}

	public function unsetVariable($name)
	{
		$this->funcContext->unsetVariable($name);
	}

	/**
	 * Get a variable.
	 *
	 * @param  string $name
	 *
	 * @return Variable|null
	 */
	public function getVariable($name)
	{
		return $this->funcContext->getVariable($name);
	}

	public function resetVariables($preserveThis = true)
	{
		$this->funcContext->resetVariables($preserveThis);
	}

	public function setReflectionFunction(ReflectionFunctionAbstract $reflFunc = null)
	{
		$this->funcContext->setReflectionFunction($reflFunc);
	}

	/**
	 * @return \ReflectionFunction
	 */
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

	public function setReflectionClass(ReflectionClass $reflClass = null)
	{
		$this->fileContext->setReflectionClass($reflClass);
	}

	/**
	 * @return \ReflectionClass
	 */
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

	/**
	 * @param  string $className
	 *
	 * @return string
	 */
	public function getClassName($className)
	{
		return $this->fileContext->getClassName($className);
	}

	public function getFileName()
	{
		return $this->fileContext->getFileName();
	}

	public function setFileName($filename)
	{
		$this->fileContext->setFileName($filename);
	}

	private function addError(Error $error)
	{
		if ($this->errors) $this->errors->add($error);
	}
}
