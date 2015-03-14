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

	/**
	 * The chain factory.
	 *
	 * @var ChainFactory
	 */
	protected $chainFactory;

	public function __construct(VisitorCollection $visitors,
		ChainFactory $chainFactory)
	{
		$this->visitors = $visitors;
		$this->chainFactory = $chainFactory;
	}

	public function addVisitor(NodeVisitorInterface $visitor)
	{
		$this->visitors->addVisitor($visitor);
	}

	public function removeVisitor($visitor)
	{
		return $this->visitors->removeVisitor($visitor);
	}

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

	public function traverseVariableChain(Node $node)
	{
		$chain = $this->chainFactory->create($node);
		return $chain->check();
	}
}
