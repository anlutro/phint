<?php
namespace Phint\Chain;

use Phint\Context\FileContext;
use Phint\ContextWrapper;
use Phint\DocblockParser;
use Phint\Error;
use Phint\ErrorBag;
use Phint\VisitorCollection;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Parser;
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
		if (count($this->links) == 1) {
			$this->isLastLink = true;
		}
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
		} elseif ($node instanceof \PhpParser\Node\Expr\New_) {
			$visitor = $this->getVisitor('NewVisitor');
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
		} elseif ($node instanceof \PhpParser\Node\Expr\New_) {
			if (!$node->class instanceof \PhpParser\Node\Name) {
				return false;
			}
			$type = $this->context->getClassName($node->class);
			if (!$type) {
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
			if (PHINT_DEBUG) {
				var_dump(__METHOD__.':'.__LINE__);
				var_dump($node);
				throw new \RuntimeException;
			}
			return false;
		}

		return $this->updateType($type);
	}

	private function getStaticMethodCallType(StaticCall $node)
	{
		if ($node->class instanceof \PhpParser\Node\Expr) {
			return false;
		}

		if (! $this->context->getReflectionClass() && (
			$node->class == 'static' ||
			$node->class == 'self' ||
			$node->class == 'parent'
		)) {
			$this->errors->add(new Error("Cannot call $node->class:: outside of class context", $node));
			return false;
		}

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
			! $reflMethod->isConstructor() &&
			$node->class != 'parent'
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
			if (PHINT_DEBUG) {
				var_dump(__METHOD__.':'.__LINE__);
				var_dump($reflector);
				throw new \RuntimeException;
			}
			return null;
		}

		if (!$type) {
			return null;
		}

		if ($file == $this->context->getFileName()) {
			$context = $this->context;
		} else {
			$context = $this->getExternalFileContext($file);
		}
		$types = $context->parseDocblockType($type);

		return $types;
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

		if ($link instanceof \PhpParser\Node\Expr\ArrayDimFetch) {
			// TODO
			return true;
		}

		if ($link->name instanceof \PhpParser\Node\Expr) {
			$this->updateType(['mixed']);
			return true;
		}

		if ($link instanceof MethodCall) {
			return $this->checkMethodCall($link);
		} elseif ($link instanceof PropertyFetch) {
			return $this->checkPropertyFetch($link);
		} elseif ($link instanceof StaticCall) {
			return $this->getStaticMethodCallType($link);
		} else {
			if (PHINT_DEBUG) {
				var_dump(__METHOD__.':'.__LINE__);
				var_dump($link);
				throw new \RuntimeException;
			}
			return false;
		}

		return true;
	}

	private function checkMethodCall(MethodCall $node)
	{
		$this->recurse(array_map(function($arg) {
			return $arg->value;
		}, $node->args));

		$reflClasses = $this->getCurrentReflectionClass();

		if (!$reflClasses) {
			$this->addMethodOnNonObjectError($node);
			return false;
		}

		if (!is_array($reflClasses)) {
			$reflClasses = [$reflClasses];
		}

		$types = [];

		foreach ($reflClasses as $reflClass) {
			if (!$reflClass->hasMethod($node->name)) {
				if (!$reflClass->hasMethod('__call')) {
					$class = $reflClass->getName();
					$this->addUndefinedMethodError($node, $class, $node->name);
				}
				return false;
			}

			$reflMethod = $reflClass->getMethod($node->name);

			$params = $reflMethod->getParameters();

			// verify number of arguments
			if (count($node->args) > count($params)) {
				// cannot error on this as php functions can use func_get_args()
			}

			$requiredParams = 0;
			foreach ($params as $param) {
				if ($param->isOptional() || $param->isDefaultValueAvailable()) {
					break;
				}
				$requiredParams++;
			}
			if (count($node->args) < $requiredParams) {
				$this->addNotEnoughParamsError($node,
					$reflClass, $requiredParams);
			}

			// look for function parameters passed by reference
			foreach ($params as $param) {
				if ($param->isPassedByReference()) {
					$pos = $param->getPosition();
					if (isset($node->args[$pos])) {
						$var = $node->args[$pos]->value;
						if ($var instanceof Variable) {
							$this->context->setVariable($var->name, $var);
						}
					}
				}
			}

			$type = $this->getReflectionType($reflMethod);

			if (!$type) {
				if (!$this->isLastLink) {
					$this->addUndeterminableTypeError($node,
						$reflClass->getName());
				}
				return false;
			}

			$types = array_merge($types, (array) $type);
		}

		return $this->updateType($types);
	}

	private function checkPropertyFetch(PropertyFetch $node)
	{
		$reflClasses = $this->getCurrentReflectionClass();

		if (!$reflClasses) {
			$this->addProperyOfNonObjectError($node);
			return false;
		}

		if (!is_array($reflClasses)) {
			$reflClasses = [$reflClasses];
		}

		$types = [];

		foreach ($reflClasses as $reflClass) {
			if (!$reflClass->hasProperty($node->name)) {
				if (!$reflClass->hasMethod('__get')) {
					$class = $reflClass->getName();
					$this->addUndefinedPropertyError($node, $class, $node->name);
				}
				return false;
			}

			$reflProperty = $reflClass->getProperty($node->name);
			$type = $this->getReflectionType($reflProperty);

			if (!$type) {
				if (!$this->isLastLink) {
					$this->addUndeterminableTypeError($node,
						$reflClass->getName());
				}
				return false;
			}

			$types = array_merge($types, (array) $type);
		}

		return $this->updateType($types);
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

	protected function recurse(array $nodes)
	{
		$visitors = $this->visitors->getAll();

		foreach ($nodes as $node) {
			foreach ($visitors as $visitor) {
				$visitor->visit($node);
			}
		}
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
		} elseif ($node instanceof Variable) {
			$nodeName = 'Variable $'.$node->name;
		} else {
			if (PHINT_DEBUG) {
				var_dump(__METHOD__.':'.__LINE__);
				var_dump($node, $className, $type);
				throw new \RuntimeException;
			}
			return;
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
		if (!PHINT_STRICT) {
			return;
		}
		$msg = "Cannot determine type of variable \${$node->name}";
		$this->errors->add(new Error($msg, $node));
	}

	private function addUndeterminableTypeError(Node $node, $class)
	{
		if (!PHINT_STRICT) {
			return;
		}
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
			if (PHINT_DEBUG) {
				var_dump(__METHOD__.':'.__LINE__);
				var_dump($node);
				throw new \RuntimeException;
			}
		}
		$msg = "Cannot determine $typeName of $nodeString";
		$this->errors->add(new Error($msg, $node));
	}

	private function addNotEnoughParamsError(MethodCall $node, ReflectionClass $reflClass, $requiredParams)
	{
		$numArgs = count($node->args);
		$msg = "Method {$reflClass->getName()}::{$node->name}() requires $requiredParams arguments, $numArgs given";
		$this->errors->add(new Error($msg, $node));
	}
}
