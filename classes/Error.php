<?php
namespace Phint;

use PhpParser\Node;

class Error
{
	protected $node;
	protected $message;

	public function __construct($message, Node $node)
	{
		$this->message = $message;
		$this->node = $node;
	}

	public function getLineNumber()
	{
		$sl = $this->node->getAttribute('startLine');
		$el = $this->node->getAttribute('endLine');

		if ($sl != $el) {
			return "$sl-$el";
		} else {
			return (string) $sl;
		}
	}

	public function getMessage()
	{
		return $this->message;
	}
}
