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
		$lastPart = $this->getLastPart($className);

		if (isset($this->imports[$lastPart])) {
			return $this->imports[$lastPart];
		}

		return null;
	}

	private function getLastPart($className)
	{
		$parts = explode('\\', $className);
		return end($parts);
	}
}
