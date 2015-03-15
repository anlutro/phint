<?php
namespace Phint;

use PhpParser\Node;

class Error
{
	/**
	 * The node that triggered the error.
	 *
	 * @var Node
	 */
	protected $node;

	/**
	 * The error message.
	 *
	 * @var string
	 */
	protected $message;

	/**
	 * Constructor.
	 *
	 * @param string $message
	 * @param Node   $node
	 */
	public function __construct($message, Node $node)
	{
		$this->message = $message;
		$this->node = $node;
	}

	/**
	 * Get the line number of the error.
	 *
	 * @return string
	 */
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

	/**
	 * Get the message of the error.
	 *
	 * @return string
	 */
	public function getMessage()
	{
		return $this->message;
	}
}
