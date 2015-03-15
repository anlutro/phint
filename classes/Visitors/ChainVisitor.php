<?php
namespace Phint\Visitors;

use Phint\AbstractNodeVisitor;
use Phint\NodeVisitorInterface;
use Phint\NodeTraverser;
use Phint\ContextWrapper;
use Phint\ErrorBag;
use Phint\Chain\ChainFactory;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;

class ChainVisitor extends AbstractNodeVisitor implements NodeVisitorInterface
{
	protected $chainFactory;

	public function __construct(
		NodeTraverser $traverser,
		ContextWrapper $context,
		ErrorBag $errors,
		ChainFactory $chainFactory
	) {
		parent::__construct($traverser, $context, $errors);
		$this->chainFactory = $chainFactory;
	}

	public function visit(Node $node)
	{
		if (
			! $node instanceof MethodCall &&
			! $node instanceof PropertyFetch
		) {
			return;
		}

		$chain = $this->chainFactory->create($node);

		if ($chain->check() === false) {
			return false;
		}

		return $chain->getCurrentType();
	}
}
