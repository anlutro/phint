<?php
use Phint\Context\FileContext;

class FileContextTest extends UnitTestCase
{
	/** @test */
	public function returns_class_name_with_namespace_appended()
	{
		$ctx = new FileContext;
		$ctx->setNamespace('Foo');
		$this->assertEquals('Foo\\Bar', $ctx->getClassName('Bar'));
	}

	/** @test */
	public function returns_fqcn_as_is()
	{
		$ctx = new FileContext;
		$ctx->setNamespace('Foo');
		$this->assertEquals('Bar', $ctx->getClassName('\\Bar'));
	}

	/** @test */
	public function checks_against_imported_classes()
	{
		$ctx = new FileContext;
		$ctx->setNamespace('Foo');
		$ctx->import('Baz\\Bar');
		$this->assertEquals('Baz\\Bar', $ctx->getClassName('Bar'));
		$ctx->import('Baz\\Bar', 'Alias');
		$this->assertEquals('Baz\\Bar', $ctx->getClassName('Alias'));
	}
}