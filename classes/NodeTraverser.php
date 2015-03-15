<?php
namespace Phint;

use PhpParser\Node;
use Phint\Chain\ChainFactory;

class NodeTraverser
{
	/**
	 * The visitor collection instance.
	 *
	 * @var VisitorCollection
	 */
	protected $visitors;

	public function __construct(VisitorCollection $visitors = null)
	{
		$this->visitors = $visitors ?: new VisitorCollection;
	}

	public function getVisitorCollection()
	{
		return $this->visitors;
	}

	public function addVisitor(NodeVisitorInterface $visitor)
	{
		$this->visitors->addVisitor($visitor);
	}

	public function removeVisitor($visitor)
	{
		return $this->visitors->removeVisitor($visitor);
	}

	/**
	 * Get a visitor.
	 *
	 * @param  string $visitor Full class name of the visitor.
	 *
	 * @return NodeVisitorInterface|null
	 */
	public function getVisitor($visitor)
	{
		return $this->visitors->getVisitor($visitor);
	}

	/**
	 * Traverse an array of nodes.
	 *
	 * @param  Node[]  $nodes
	 *
	 * @return void
	 */
	public function traverse(array $nodes)
	{
		foreach ($nodes as $node) {
			foreach ($this->visitors->getAll() as $visitor) {
				$visitor->visit($node);
			}
		}
	}
}
