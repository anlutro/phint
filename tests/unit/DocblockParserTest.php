<?php
use Phint\DocblockParser;

class DocblockParserTest extends UnitTestCase
{
	/** @test */
	public function can_parse_param_docblocks()
	{
		$docblock = '/** @param Foo $foo */';
		$this->assertEquals('Foo', DocblockParser::getParamType($docblock, '$foo'));
		$this->assertEquals(null, DocblockParser::getParamType($docblock, '$bar'));

		$docblock = '/**
		* @param Foo $foo
		* @param Bar $bar
		*/';
		$this->assertEquals('Foo', DocblockParser::getParamType($docblock, '$foo'));
		$this->assertEquals('Bar', DocblockParser::getParamType($docblock, '$bar'));
	}

	/** @test */
	public function can_parse_return_docblocks()
	{
		$docblock = '/** blah blah */';
		$this->assertEquals(null, DocblockParser::getMethodType($docblock));

		$docblock = '/** @return Foo */';
		$this->assertEquals('Foo', DocblockParser::getMethodType($docblock));

		$docblock = '/**
		* @param Foo $foo
		* @return Bar
		*/';
		$this->assertEquals('Bar', DocblockParser::getMethodType($docblock));
	}

	/** @test */
	public function can_parse_var_docblocks()
	{
		$docblock = '/** blah blah */';
		$this->assertEquals(null, DocblockParser::getPropertyType($docblock));

		$docblock = '/** @var Foo */';
		$this->assertEquals('Foo', DocblockParser::getPropertyType($docblock));

		$docblock = '/** @var Foo $foo */';
		$this->assertEquals('Foo', DocblockParser::getPropertyType($docblock));

		$docblock = '/**
		* blah blah
		* @var Bar
		*/';
		$this->assertEquals('Bar', DocblockParser::getPropertyType($docblock));
	}
}
