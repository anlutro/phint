<?php
namespace Phint\Context;

class ImportBag
{
	protected $imports = [];

	public function add($className, $alias = null)
	{
		if ($alias) {
			$key = $alias;
		} else {
			$key = $this->getLastPart($className);
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

	private function getLastPart($className)
	{
		$parts = explode('\\', $className);
		return end($parts);
	}
}
