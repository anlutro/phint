<?php
namespace Phint;

use Phint\Context\FunctionContext;
use PhpParser\Node;

abstract class AbstractNodeVisitor
{
	/**
	 * The node traverser instance.
	 *
	 * @var NodeTraverser
	 */
	private $traverser;

	/**
	 * The context wrapper instance.
	 *
	 * @var ContextWrapper
	 */
	private $context;

	/**
	 * The error bag instance.
	 *
	 * @var ErrorBag
	 */
	private $errors;

	public function __construct(
		NodeTraverser $traverser,
		ContextWrapper $context,
		ErrorBag $errors
	) {
		$this->traverser = $traverser;
		$this->context = $context;
		$this->errors = $errors;
	}

	/**
	 * @return ContextWrapper
	 */
	protected function getContext()
	{
		return $this->context;
	}

	/**
	 * Clone the current function context.
	 *
	 * @return Context\FunctionContext
	 */
	protected function cloneContext()
	{
		return clone $this->context->getFunctionContext();
	}

	protected function addError(Error $error)
	{
		$this->errors->add($error);
	}

	protected function recurse()
	{
		foreach (func_get_args() as $nodes) {
			if (!is_array($nodes)) {
				$nodes = [$nodes];
			}
			$this->traverser->traverse($nodes);
		}
	}

	protected function recurseWithNewContext(FunctionContext $newContext, array $nodes)
	{
		$oldContext = $this->context->getFunctionContext();
		$this->context->setFunctionContext($newContext);
		$this->recurse($nodes);
		$this->context->setFunctionContext($oldContext);
	}

	protected function traverseVariableChain(Node $node)
	{
		return $this->traverser->traverseVariableChain($node);
	}
}
