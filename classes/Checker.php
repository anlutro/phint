<?php
namespace Phint;

use PhpParser\Parser;
use PhpParser\Lexer;

class Checker
{
	protected $parser;
	protected $wrapper;
	protected $errors;
	protected $traverser;

	public function __construct(
		Parser $parser = null,
		ContextWrapper $wrapper = null,
		ErrorBag $errors = null,
		NodeTraverser $traverser = null
	) {
		$this->parser = $parser ?: new Parser(new Lexer);
		$this->wrapper = $wrapper ?: new ContextWrapper();
		$this->errors = $errors ?: new ErrorBag();
		$this->traverser = $traverser ?: new NodeTraverser();
	}

	public function addVisitor($visitor)
	{
		if (is_string($visitor)) {
			$visitor = new $visitor($this->traverser, $this->wrapper, $this->errors);
		}
		if (! $visitor instanceof NodeVisitorInterface) {
			throw new \InvalidArgumentException('Visitor must be instance of NodeVisitorInterface');
		}
		$this->traverser->addVisitor($visitor);
	}

	public function addDefaultVisitors()
	{
		$this->addVisitor('Phint\Visitors\NamespaceVisitor');
		$this->addVisitor('Phint\Visitors\UseVisitor');
		$this->addVisitor('Phint\Visitors\ClassVisitor');
		$this->addVisitor('Phint\Visitors\ClassMethodVisitor');
		$this->addVisitor('Phint\Visitors\VariableVisitor');
		$this->addVisitor('Phint\Visitors\AssignVisitor');
		$this->addVisitor('Phint\Visitors\BooleanNotVisitor');
		$this->addVisitor('Phint\Visitors\BinaryOpVisitor');
		$this->addVisitor('Phint\Visitors\TernaryVisitor');
		$this->addVisitor('Phint\Visitors\InstanceofVisitor');
		$this->addVisitor('Phint\Visitors\StringWithVariableVisitor');
		$this->addVisitor('Phint\Visitors\ObjectPropertyVisitor');
		$this->addVisitor('Phint\Visitors\MethodCallVisitor');
		$this->addVisitor('Phint\Visitors\NewVisitor');
		$this->addVisitor('Phint\Visitors\FunctionCallVisitor');
		$this->addVisitor('Phint\Visitors\ReturnVisitor');
		$this->addVisitor('Phint\Visitors\IfVisitor');
		$this->addVisitor('Phint\Visitors\WhileVisitor');
		$this->addVisitor('Phint\Visitors\ForVisitor');
		$this->addVisitor('Phint\Visitors\ForeachVisitor');
		$this->addVisitor('Phint\Visitors\TryCatchVisitor');
		$this->addVisitor('Phint\Visitors\SwitchVisitor');
		$this->addVisitor('Phint\Visitors\ClosureVisitor');
	}

	public function check($path)
	{
		$this->errors->clear();
		$code = file_get_contents($path);
		$nodes = $this->parser->parse($code);
		$this->traverser->traverse($nodes);
	}

	public function getErrors()
	{
		return $this->errors->getAll();
	}
}
