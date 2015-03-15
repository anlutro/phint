<?php
namespace Phint\Chain;

use Phint\Context\ImportBag;

class ExternalFileContext
{
	/** @var ImportBag */
	protected $imports;
	/** @var string */
	protected $namespace;
	/** @var string */
	protected $class;

	public function __construct(array $nodes)
	{
		$this->imports = new ImportBag;
		$this->scanNodes($nodes);
	}

	private function scanNodes(array $nodes)
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
				return ($this->namespace ? $this->namespace.'\\' : '').$node->name;
			}
		}
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
			return $this->class;
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
