<?php
namespace Phint\Chain;

use Phint\ContextWrapper;
use Phint\Error;
use Phint\ErrorBag;
use Phint\DocblockParser;
use Phint\VisitorCollection;
use PhpParser\Parser;
use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;

class Chain
{
	protected static $externalFileContexts = [];

	protected $parser;
	protected $visitors;
	protected $context;
	protected $errors;
	protected $links;
	protected $currentLink;
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

	public function check()
	{
		if ($this->checkInitialNode($this->links[0]) === false) {
			return false;
		}

		$links = array_slice($this->links, 1, -1);
		if ($this->checkLinks($links) === false) {
			return false;
		}

		$links = array_slice($this->links, -1);
		if ($this->checkLastLink($links[0]) === false) {
			return false;
		}

		return $this->getCurrentType();
	}

	private function checkInitialNode(Node $node)
	{
		// var_dump($node);
		$this->currentLink = $node;

		if ($node instanceof Variable) {
			$visitor = $this->getVisitor('VariableVisitor');
		} elseif ($node instanceof StaticCall) {
			$visitor = $this->getVisitor('StaticMethodCallVisitor');
		} elseif ($node instanceof FuncCall) {
			$visitor = $this->getVisitor('FunctionCallVisitor');
		}

		if (isset($visitor)) {
			if ($visitor->visit($node) === false) {
				return false;
			}
		}

		if ($node instanceof Variable) {
			$ctxVar = $this->context->getVariable($node->name);
			$type = $ctxVar->getType();
			if (!$type) {
				$this->addUndeterminableVariableTypeError($node);
				return false;
			}
			$type = (array) $type;
		} elseif ($node instanceof StaticCall) {
			$type = $this->getStaticMethodCallType($node);
			if (!$type) {
				return false;
			}
		} elseif ($node instanceof FuncCall) {
			die("TODO: Not yet implemented\n");
		} else {
			var_dump(__METHOD__.':'.__LINE__);
			var_dump($node); die();
		}

		return $this->updateType($type);
	}

	private function getStaticMethodCallType(StaticCall $node)
	{
		$className = $this->context->getClassName($node->class);
		$reflClass = new \ReflectionClass($className);

		if (!$reflClass->hasMethod($node->name)) {
			if (!$reflClass->hasMethod('__callStatic')) {
				$this->addUndefinedMethodError($node, $className, $node->name);
			}
			return false;
		}

		$reflMethod = $reflClass->getMethod($node->name);
		if (!$reflMethod->isStatic()) {
			if (!$reflClass->hasMethod('__callStatic')) {
				$this->addStaticCallNonStaticMethodError($node, $className, $node->name);
			}
			return false;
		}

		$type = $this->getReflectionType($reflMethod);

		if (!$type) {
			if (!$this->isLastLink) {
				$className = $this->getCurrentReflectionClass()
					->getName();
				$this->addUndeterminableTypeError($node, $className);
			}
			return false;
		}

		return $type;
	}

	private function getReflectionType($reflector)
	{
		$docstr = $reflector->getDocComment();
		if (!$docstr) {
			return false;
		}

		if ($reflector instanceof \ReflectionMethod) {
			$file = $reflector->getFileName();
			$type = DocblockParser::getMethodType($docstr);
		} elseif ($reflector instanceof \ReflectionProperty) {
			$file = $reflector->getDeclaringClass()->getFileName();
			$type = DocblockParser::getPropertyType($docstr);
		} else {
			var_dump(__METHOD__.':'.__LINE__);
			var_dump($reflector); die();
		}

		if (!$type) {
			return null;
		}

		$types = explode('|', $type);
		$finalTypes = [];
		foreach ($types as $type) {
			if (\Phint\Context\Variable::isClassType($type)) {
				if ($file != $this->context->getFileName()) {
					$context = $this->getExternalFileContext($file);
					$type = $context->getClassName($type);
				} else {
					$type = $this->context->getClassName($type);
				}
			}

			$finalTypes[] = $type;
		}

		return $finalTypes;
	}

	private function getExternalFileContext($file)
	{
		if (!isset(static::$externalFileContexts[$file])) {
			$nodes = $this->parser->parse(file_get_contents($file));
			$ctx = new ExternalFileContext($nodes);
			static::$externalFileContexts[$file] = $ctx;
		}

		return static::$externalFileContexts[$file];
	}

	private function updateType(array $types)
	{
		$reflClass = [];
		foreach ($types as $key => $type) {
			$arrayOf = (substr($type, -2) == '[]');

			if (\Phint\Context\Variable::isClassType($type)) {
				$typeClass = $arrayOf ? substr($type, 0, -2) : $type;
				if (!$this->classExists($typeClass)) {
					$currentClass = $this->getCurrentReflectionClass();
					$currentClass = $currentClass ? $currentClass->getName() : null;
					$this->addClassNotFoundError($typeClass, $currentClass,
						$this->currentLink);
					return false;
				}
				if (!$arrayOf) {
					$reflClass[] = new \ReflectionClass($type);
				}
			}
		}

		$this->currentReflClass = $reflClass;
		$this->currentType = $types;
		return true;
	}

	private function classExists($className)
	{
		return class_exists($className) || interface_exists($className);
	}

	private function getVisitor($visitor)
	{
		return $this->visitors->getVisitor('Phint\\Visitors\\'.$visitor);
	}

	private function checkLinks(array $links)
	{
		foreach ($links as $link) {
			if ($this->checkLink($link) === false) {
				return false;
			}
		}
	}

	private function checkLastLink(Node $link)
	{
		$this->isLastLink = true;
		return $this->checkLink($link);
	}

	private function checkLink(Node $link)
	{
		$this->currentLink = $link;
		
		if ($link instanceof MethodCall) {
			return $this->checkMethodCall($link);
		} elseif ($link instanceof PropertyFetch) {
			return $this->checkPropertyFetch($link);
		} else {
			var_dump(__METHOD__.':'.__LINE__);
			var_dump($link); die();
		}

		return true;
	}

	private function checkMethodCall(MethodCall $node)
	{
		$currentReflClass = $this->getCurrentReflectionClass();

		if (!$currentReflClass) {
			$this->addMethodOnNonObjectError($node);
			return false;
		}

		if (!$currentReflClass->hasMethod($node->name)) {
			if (!$currentReflClass->hasMethod('__call')) {
				$class = $currentReflClass->getName();
				$this->addUndefinedMethodError($node, $class, $node->name);
			}
			return false;
		}

		$reflMethod = $currentReflClass->getMethod($node->name);
		$type = $this->getReflectionType($reflMethod);

		if (!$type) {
			if (!$this->isLastLink) {
				$this->addUndeterminableTypeError($node,
					$currentReflClass->getName());
			}
			return false;
		}

		return $this->updateType($type);
	}

	private function checkPropertyFetch(PropertyFetch $node)
	{
		$currentReflClass = $this->getCurrentReflectionClass();

		if (!$currentReflClass) {
			$this->addProperyOfNonObjectError($node);
			return false;
		}

		if (!$currentReflClass->hasProperty($node->name)) {
			if (!$currentReflClass->hasMethod('__get')) {
				$class = $currentReflClass->getName();
				$this->addUndefinedPropertyError($node, $class, $node->name);
			}
			return false;
		}

		$reflProperty = $currentReflClass->getProperty($node->name);
		$type = $this->getReflectionType($reflProperty);

		if (!$type) {
			if (!$this->isLastLink) {
				$this->addUndeterminableTypeError($node,
					$currentReflClass->getName());
			}
			return false;
		}

		return $this->updateType($type);
	}

	private function getCurrentType()
	{
		return count($this->currentType) > 1 || !$this->currentType
			? $this->currentType : $this->currentType[0];
	}

	private function getCurrentReflectionClass()
	{
		return count($this->currentReflClass) > 1 || !$this->currentReflClass
			? $this->currentReflClass : $this->currentReflClass[0];
	}

	private function addMethodOnNonObjectError(MethodCall $node)
	{
		$msg = "Trying to call method on non-object";
		$this->errors->add(new Error($msg, $node));
	}

	private function addClassNotFoundError($type, $className, Node $node)
	{
		$className = ltrim($className, '\\');
		$type = ltrim($type, '\\');
		if ($node instanceof MethodCall) {
			$nodeName = 'Method '.$className.'::'.$node->name.'()';
		} elseif ($node instanceof PropertyFetch) {
			$nodeName = 'Property '.$className.'::$'.$node->name;
		} else {
			var_dump(__METHOD__.':'.__LINE__);
			var_dump($node); die();
		}
		$msg = "$nodeName is type-hinted against a non-existant class: $type";
		$this->errors->add(new Error($msg, $node));
	}

	private function addUndefinedMethodError(Node $node, $class, $method)
	{
		$msg = "Call to undefined method: $class::$method()";
		$this->errors->add(new Error($msg, $node));
	}

	private function addStaticCallNonStaticMethodError(Node $node, $class, $method)
	{
		$msg = "Calling non-static method statically: $class::$method()";
		$this->errors->add(new Error($msg, $node));
	}

	private function addProperyOfNonObjectError(PropertyFetch $node)
	{
		$msg = "Trying to get property of non-object";
		$this->errors->add(new Error($msg, $node));
	}

	private function addUndefinedPropertyError(PropertyFetch $node, $class, $property)
	{
		$msg = "Undefined property: $class::\$$property";
		$this->errors->add(new Error($msg, $node));
	}

	private function addUndeterminableVariableTypeError(Variable $node)
	{
		$msg = "Cannot determine type of variable \${$node->name}";
		$this->errors->add(new Error($msg, $node));
	}

	private function addUndeterminableTypeError(Node $node, $class)
	{
		if ($node instanceof MethodCall) {
			$typeName = 'return value type';
			$nodeString = "method {$class}::{$node->name}()";
		} elseif ($node instanceof PropertyFetch) {
			$typeName = 'type';
			$nodeString = "property {$class}::\${$node->name}";
		} else {
			var_dump(__METHOD__.':'.__LINE__);
			var_dump($node); die();
		}
		$msg = "Undeterminable $typeName of $nodeString";
		$this->errors->add(new Error($msg, $node));
	}
}
