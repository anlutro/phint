<?php
namespace Phint;

class DocblockParser
{
	const PARAM_REGEX = '/\@param\s+([a-zA-Z\\\\\|\[\]]+)\s+(\$[a-zA-Z_]+).*/';

	public static function getParamType($docblock, $paramName)
	{
		if ($paramName[0] != '$') {
			$paramName = '$'.$paramName;
		}

		preg_match_all(static::PARAM_REGEX, $docblock, $matches);
		if (!$matches) {
			return null;
		}

		foreach ($matches[2] as $key => $value) {
			if ($value === $paramName) {
				return $matches[1][$key];
			}
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
