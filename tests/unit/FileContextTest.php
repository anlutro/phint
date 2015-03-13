<?php
use Phint\Context\FileContext;

class FileContextTest extends UnitTestCase
{
	/** @test */
	public function returnsClassNameWithNamespaceAppended()
	{
		$ctx = new FileContext;
		$ctx->setNamespace('Foo');
		$this->assertEquals('Foo\\Bar', $ctx->getClassName('Bar'));
	}

	/** @test */
	public function returnsFQCNasIs()
	{
		$ctx = new FileContext;
		$ctx->setNamespace('Foo');
		$this->assertEquals('\\Bar', $ctx->getClassName('\\Bar'));
	}

	/** @test */
	public function checksAgainstImportedClasses()
	{
		$ctx = new FileContext;
		$ctx->setNamespace('Foo');
		$ctx->import('Baz\\Bar');
		$this->assertEquals('Baz\\Bar', $ctx->getClassName('Bar'));
		$ctx->import('Baz\\Bar', 'Alias');
		$this->assertEquals('Baz\\Bar', $ctx->getClassName('Alias'));
	}
}