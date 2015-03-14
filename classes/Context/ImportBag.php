<?php
namespace Phint\Context;

class ImportBag
{
	protected $imports = [];

	public function add($className, $alias = null)
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

	public function findImportedClassName($className)
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
