<?php
namespace Phint\Chain;

use Phint\ContextWrapper;
use Phint\ErrorBag;
use Phint\VisitorCollection;
use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Parser;

class ChainFactory
{
	protected $parser;
	protected $visitors;
	protected $context;
	protected $errors;

	public function __construct(
		Parser $parser,
		VisitorCollection $visitors,
		ContextWrapper $context,
		ErrorBag $errors
	) {
		$this->parser = $parser;
		$this->visitors = $visitors;
		$this->context = $context;
		$this->errors = $errors;
	}

	public function create(Node $node)
	{
		$links = [];
		$link = $node;

		while (in_array('var', $link->getSubnodeNames())) {
			$links[] = $link;
			$link = $link->var;
		}

		$links[] = $link;
		$links = array_reverse($links);

		$chain = new Chain($this->parser, $this->visitors,
			$this->context, $this->errors, $links);

		return $chain;
	}
}
