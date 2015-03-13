<?php
namespace Phint\Visitors;

use Phint\AbstractNodeVisitor;
use Phint\NodeVisitorInterface;
use PhpParser\Node;
use PhpParser\Node\Scalar\Encapsed;

class StringWithVariableVisitor extends AbstractNodeVisitor implements NodeVisitorInterface
{
	public function visit(Node $node)
	{
		if (! $node instanceof Encapsed) {
			return;
		}

		$parts = array_filter($node->parts, function($part) {
			return $part instanceof Node;
		});
		$this->recurse($parts);
	}
}
