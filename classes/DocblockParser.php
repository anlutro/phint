<?php
namespace Phint;

class DocblockParser
{
	public static function getParamType($docblock, $paramName)
	{
		while (($pos = strpos($docblock, '@param')) !== false) {
			$substr = substr($docblock, $pos);
			$substr = substr($substr, 0, $pos = strpos($substr, "\n"));
			preg_match('/\@param\s+([a-zA-Z\\\\\|\[\]]+)\s+(\$[a-zA-Z_]+).*/', $substr, $matches);
			if ($matches) {
				$type = $matches[1];
				$name = $matches[2];
				if ($name == '$'.$paramName) {
					return $type;
				}
			}
			$docblock = substr($docblock, $pos);
		}

		return null;
	}

	public static function getVariableType($docblock)
	{
		return static::getDocblockType($docblock, '@var');
	}

	public static function getPropertyType($docblock)
	{
		return static::getDocblockType($docblock, '@var');
	}

	public static function getMethodType($docblock)
	{
		return static::getDocblockType($docblock, '@return');
	}

	protected static function getDocblockType($docblock, $search)
	{
		$pos = strpos($docblock, $search);
		if ($pos === false) {
			return null;
		}

		$docblock = substr($docblock, $pos);
		preg_match('/\\'.$search.'\s+([a-zA-Z\\\\\|\[\]]+).*/', $docblock, $matches);

		if (isset($matches[1])) {
			return $matches[1];
		}

		return false;
	}
}
