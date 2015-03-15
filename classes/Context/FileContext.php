<?php
namespace Phint\Context;

use ReflectionClass;
use PhpParser\Node\Stmt\Class_ as ClassNode;

class FileContext
{
	protected $filename;
	protected $namespace;
	protected $imports;
	protected $classNode;
	protected $reflectionClass;

	public function __construct(ImportBag $imports = null)
	{
		$this->imports = $imports ?: new ImportBag;
	}

	public function getFileName()
	{
		return $this->filename;
	}

	public function setFileName($filename)
	{
		$this->filename = $filename;
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
	public function setReflectionClass(ReflectionClass $reflectionClass = null)
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
		$this->imports->add($className, $alias);
	}

	public function getClassName($className)
	{
		if ($className instanceof \PhpParser\Node\Name) {
			if ($className->isFullyQualified()) {
				return $className->toString();
			}

			$className = $className->toString();
		}

		if ($className == 'static' || $className == 'self') {
			return $this->reflectionClass->getName();
		}

		if ('\\' == $className[0]) {
			return ltrim($className, '\\');
		}

		if ($importedClass = $this->imports->findImportedClassName($className)) {
			return $importedClass;
		}

		return ($this->namespace ? $this->namespace.'\\' : '').$className;
	}
}
