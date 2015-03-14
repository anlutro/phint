<?php
namespace Phint\Context;

use ReflectionClass;
use PhpParser\Node\Stmt\Class_ as ClassNode;

class FileContext
{
	protected $imports;
	protected $classNode;
	protected $reflectionClass;
	protected $namespace;

	public function __construct(
		ImportBag $imports = null
	) {
		$this->imports = $imports ?: new ImportBag();
	}

	/**
	 * Get the class node.
	 *
	 * @return ClassNode
	 */
	public function getClassNode()
	{
		return $this->classNode;
	}
	
	/**
	 * Set the class node.
	 *
	 * @param ClassNode $classNode
	 */
	public function setClassNode(ClassNode $classNode)
	{
		$this->classNode = $classNode;
	}

	/**
	 * Get the reflection class.
	 *
	 * @return ReflectionClass
	 */
	public function getReflectionClass()
	{
		return $this->reflectionClass;
	}
	
	/**
	 * Set the reflection class.
	 *
	 * @param ReflectionClass $reflectionClass
	 */
	public function setReflectionClass(ReflectionClass $reflectionClass)
	{
		$this->reflectionClass = $reflectionClass;
	}

	/**
	 * Get the namespace.
	 *
	 * @return string
	 */
	public function getNamespace()
	{
		return $this->namespace;
	}
	
	/**
	 * Set the namespace.
	 *
	 * @param string $namespace
	 */
	public function setNamespace($namespace)
	{
		$this->namespace = $namespace;
	}

	public function import($className, $alias = null)
	{
		$className = ltrim($className, '\\');
		$this->imports->add($className, $alias);
	}

	public function getClassName($className)
	{
		if ($className instanceof \PhpParser\Node\Name) {
			if ($className->isFullyQualified()) {
				$className = '\\'.$className->toString();
			} else {
				$className = $className->toString();
			}
		}

		if ('\\' == $className[0]) {
			return substr($className, 1);
		}

		if ($importedClass = $this->imports->findImportedClassName($className)) {
			return $importedClass;
		}

		return ($this->namespace ? $this->namespace.'\\' : '').$className;
	}
}
