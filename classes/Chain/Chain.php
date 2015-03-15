<?php
namespace Phint\Chain;

use Phint\ContextWrapper;
use Phint\Error;
use Phint\ErrorBag;
use Phint\DocblockParser;
use Phint\VisitorCollection;
use Phint\Context\FileContext;
use PhpParser\Parser;
use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

class Chain
{
	protected static $externalFileContexts = [];

	/** @var Parser */
	protected $parser;
	/** @var VisitorCollection */
	protected $visitors;
	/** @var ContextWrapper */
	protected $context;
	/** @var ErrorBag */
	protected $errors;
	/** @var array */
	protected $links;
	/** @var Node */
	protected $currentLink;
	/** @var string[] */
	protected $currentType;
	/** @var ReflectionClass[] */
	protected $currentReflClass;
	/** @var boolean */
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

		return true;
	}

	private function checkInitialNode(Node $node)
	{
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
		$reflClass = new ReflectionClass($className);

		if (!$reflClass->hasMethod($node->name)) {
			if (!$reflClass->hasMethod('__callStatic')) {
				$this->addUndefinedMethodError($node, $className, $node->name);
			}
			return false;
		}

		$reflMethod = $reflClass->getMethod($node->name);
		if (
			! $reflMethod->isStatic() &&
			! $reflMethod->isConstructor()
		) {
			if (!$reflClass->hasMethod('__callStatic')) {
				$this->addStaticCallNonStaticMethodError($node, $className, $node->name);
			}
			return false;
		}

		$type = $this->getReflectionType($reflMethod);

		if (!$type) {
			// don't add an error if we're at the last link. the error may be
			// recoverable or insignificant
			if (!$this->isLastLink) {
				$this->addUndeterminableTypeError($node, $className);
			}
			return false;
		}

		return $type;
	}

	private function getReflectionType($reflector)
	{
		if (
			$reflector instanceof ReflectionMethod &&
			$reflector->isConstructor()
		) {
			$className = $reflector->getDeclaringClass()->getName();
			return [$className];
		}

		$docstr = $reflector->getDocComment();
		if (!$docstr) {
			return false;
		}

		if ($reflector instanceof ReflectionMethod) {
			$file = $reflector->getFileName();
			$type = DocblockParser::getMethodType($docstr);
		} elseif ($reflector instanceof ReflectionProperty) {
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
			$typeWithoutArray = str_replace('[]', '', $type);
			if (\Phint\Context\Variable::isClassType($typeWithoutArray)) {
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

	/**
	 * @param  string $file
	 *
	 * @return FileContext
	 */
	private function getExternalFileContext($file)
	{
		if (!isset(static::$externalFileContexts[$file])) {
			$nodes = $this->parser->parse(file_get_contents($file));
			$ctx = FileContext::createFromNodes($nodes);
			static::$externalFileContexts[$file] = $ctx;
		}

		return static::$externalFileContexts[$file];
	}

	private function updateType(array $types)
	{
		$reflClass = [];
		foreach ($types as $key => $type) {
			$arrayOf = (substr($type, -2) == '[]');
			$typeWithoutArray = str_replace('[]', '', $type);

			if (\Phint\Context\Variable::isClassType($typeWithoutArray)) {
				if (!$this->classExists($typeWithoutArray)) {
					$currentClass = $this->getCurrentReflectionClass();
					$currentClass = $currentClass ? $currentClass->getName() : null;
					$this->addClassNotFoundError($typeWithoutArray, $currentClass,
						$this->currentLink);
					return false;
				}
				if (!$arrayOf) {
					$reflClass[] = new ReflectionClass($type);
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

	/**
	 * Get a visitor.
	 *
	 * @param  string $visitor
	 *
	 * @return \Phint\NodeVisitorInterface|null
	 */
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
		} elseif ($link instanceof StaticCall) {
			return $this->getStaticMethodCallType($link);
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

	public function getCurrentType()
	{
		return count($this->currentType) > 1 || !$this->currentType
			? $this->currentType : $this->currentType[0];
	}

	/**
	 * @return ReflectionClass|ReflectionClass[]|null
	 */
	public function getCurrentReflectionClass()
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
		} elseif ($node instanceof StaticCall) {
			$typeName = 'return value type';
			$nodeString = "static method {$class}::{$node->name}()";
		} elseif ($node instanceof PropertyFetch) {
			$typeName = 'type';
			$nodeString = "property {$class}::\${$node->name}";
		} else {
			var_dump(__METHOD__.':'.__LINE__);
			var_dump($node); die();
		}
		$msg = "Cannot determine $typeName of $nodeString";
		$this->errors->add(new Error($msg, $node));
	}
}
