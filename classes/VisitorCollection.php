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

	/**
	 * Get a visitor.
	 *
	 * @param  string $visitor
	 *
	 * @return NodeVisitorInterface
	 */
	public function getVisitor($visitor)
	{
		if (isset($this->visitors[$visitor])) {
			return $this->visitors[$visitor];
		}
		
		return null;
	}

	/**
	 * Get an array of all the visitors.
	 *
	 * @return NodeVisitorInterface[]
	 */
	public function getAll()
	{
		return $this->visitors;
	}
}
