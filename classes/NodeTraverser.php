<?php
namespace Phint;

class NodeTraverser
{
	protected $visitors = [];

	public function addVisitor(NodeVisitorInterface $visitor)
	{
		$this->visitors[] = $visitor;
	}

	public function traverse(array $nodes)
	{
		foreach ($nodes as $node) {
			foreach ($this->visitors as $visitor) {
				$visitor->visit($node);
			}
		}
	}
}
