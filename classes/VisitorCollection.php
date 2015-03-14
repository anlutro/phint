<?php
namespace Phint;

class VisitorCollection
{
	protected $visitors = [];

	public function addVisitor(NodeVisitorInterface $visitor)
	{
		$this->visitors[get_class($visitor)] = $visitor;
	}

	public function removeVisitor($visitor)
	{
		if (isset($this->visitors[$visitor])) {
			unset($this->visitors[$visitor]);
			return true;
		}

		return false;
	}

	public function getVisitor($visitor)
	{
		if (isset($this->visitors[$visitor])) {
			return $this->visitors[$visitor];
		}
		
		return null;
	}

	public function getAll()
	{
		return $this->visitors;
	}
}
