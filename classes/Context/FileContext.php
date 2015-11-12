<?php
namespace Phint\Context;

use ReflectionClass;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_ as ClassNode;

class FileContext
{
	/** @var string */
	protected $filename;

	/** @var array */
	protected $lines;

	/** @var string|null */
	protected $namespace;

	/** @var ImportBag */
	protected $imports;

	/** @var ClassNode|null */
	protected $classNode;

	/** @var ReflectionClass|null */
	protected $reflectionClass;

	public function __construct(ImportBag $imports = null)
	{
		$this->imports = $imports ?: new ImportBag;
	}

	/**
	 * Create a new context instance from an array of nodes.
	 *
	 * @param  Node[]  $nodes
	 *
	 * @return FileContext
	 */
	public static function createFromNodes(array $nodes)
	{
		$context = new static;
		$context->scanNodes($nodes);
		return $context;
	}

	protected function scanNodes(array $nodes)
	{
		foreach ($nodes as $node) {
			if ($node instanceof \PhpParser\Node\Stmt\Namespace_) {
				$this->namespace = (string) $node->name;
				$this->scanNodes($node->stmts);
			}

			if ($node instanceof \PhpParser\Node\Stmt\Use_) {
				foreach ($node->uses as $use) {
					$this->imports->add($use->name->toString(), $use->alias);
				}
			}

			if ($node instanceof \PhpParser\Node\Stmt\Class_) {
				$this->classNode = $node;
				$className = ($this->namespace ? $this->namespace.'\\' : '').$node->name;
				$this->reflectionClass = new ReflectionClass($className);
			}

			if ($node instanceof \PhpParser\Node\Stmt\Interface_) {
				$this->classNode = $node;
				$className = ($this->namespace ? $this->namespace.'\\' : '').$node->name;
				$this->reflectionClass = new ReflectionClass($className);
			}
		}
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

		if (!$className) {
			throw new \InvalidArgumentException('$className cannot be empty');
		}

		if ($className == 'static' || $className == 'self') {
			return $this->reflectionClass->getName();
		}

		if ($className == 'parent') {
			$parentClass = $this->reflectionClass->getParentClass();
			return $parentClass ? $parentClass->getName() : null;
		}

		// cannot determine classnames of expressions, as they are variable
		if ($className instanceof \PhpParser\Node\Expr) {
			return null;
		}

		if ('\\' == $className[0]) {
			return ltrim($className, '\\');
		}

		if ($importedClass = $this->imports->findImportedClassName($className)) {
			return $importedClass;
		}

		return ($this->namespace ? $this->namespace.'\\' : '').$className;
	}

	public function parseDocblockType($type)
	{
		$types = explode('|', $type);
		$finalTypes = [];

		foreach ($types as $type) {
			$isArray = strpos($type, '[]') !== false;
			$typeWithoutArray = str_replace('[]', '', $type);
			if (\Phint\Context\Variable::isClassType($typeWithoutArray)) {
				$type = $this->getClassName($typeWithoutArray);
				if ($isArray) {
					$type .= '[]';
				}
			}

			$finalTypes[] = $type;
		}

		return $finalTypes;
	}
}
