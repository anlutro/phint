<?php
namespace Phint\Chain;

use PhpParser\Parser;
use Phint\VisitorCollection;
use Phint\ContextWrapper;
use Phint\ErrorBag;

class Chain
{
	protected $parser;
	protected $visitors;
	protected $context;
	protected $errors;
	protected $links;

	protected $externalFileContexts = [];
	protected $currentType;
	protected $currentReflClass;
	protected $isLastLink = false;

	public function __construct(
		Parser $parser,
		VisitorCollection $visitors,
		ContextWrapper $context,
		ErrorBag $errors,
		array $links
	) {
		$this->parser = $parser;
		$this->visitors = $visitors;
		$this->context = $context;
		$this->errors = $errors;
		$this->links = $links;
	}
}
