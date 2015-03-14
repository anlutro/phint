<?php
namespace Phint;

use Phint\Context\FunctionContext;

abstract class AbstractNodeVisitor
{
	private $traverser;
	private $context;
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

	protected function getContext()
	{
		return $this->context;
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
}
