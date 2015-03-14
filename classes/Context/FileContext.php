<?php
namespace Phint\Context;

use ReflectionClass;
use PhpParser\Node\Stmt\Class_ as ClassNode;

class FileContext
{
	protected $imports = [];
	protected $classNode;
	protected $reflectionClass;
	protected $namespace;

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

		if ($alias) {
			$key = $alias;
		} else {
			$parts = explode('\\', $className);
			$key = end($parts);
		}

		$this->imports[$key] = $className;
	}

	public function getClassName($className)
	{
		if ($className instanceof \PhpParser\Node\Name) {
			if ($className->isFullyQualified()) {
				return $className->toString();
			}

			$className = $className->toString();
		}

		if ('\\' == $className[0]) {
			return ltrim($className, '\\');
		}

		if ($importedClass = $this->findImportedClassName($className)) {
			return $importedClass;
		}

		return ($this->namespace ? $this->namespace.'\\' : '').$className;
	}

	private function findImportedClassName($className)
	{
		$parts = explode('\\', $className);
		$firstPart = $parts[0];

		if (isset($this->imports[$firstPart])) {
			$parts[0] = $this->imports[$firstPart];
			return implode('\\', $parts);
		}

		return null;
	}
}
