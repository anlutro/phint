<?php
namespace Phint;

class DocblockParser
{
	const CLASS_REGEX = '[a-zA-Z\\\\\_\|\[\]]+';
	const VAR_REGEX = '\$[a-zA-Z_]+';

	public static function getParamType($docblock, $paramName)
	{
		if ($paramName[0] != '$') {
			$paramName = '$'.$paramName;
		}

		$pattern = '/\@param\s+('.static::CLASS_REGEX.')\s+('.static::VAR_REGEX.').*/';
		preg_match_all($pattern, $docblock, $matches);
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
		preg_match('/\\'.$search.'\s+('.static::CLASS_REGEX.').*/', $docblock, $matches);

		if (isset($matches[1])) {
			return $matches[1];
		}

		return false;
	}
}
