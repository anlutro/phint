<?php
namespace Phint;

use PhpParser\Node;

interface NodeVisitorInterface
{
	public function visit(Node $node);
}
