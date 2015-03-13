<?php
class NamespaceTest extends FunctionalTestCase
{
	/** @test */
	public function regenerate()
	{
		$errors = $this->check(__DIR__.'/cases/NamespaceCase.php');
		$this->assertEquals(0, count($errors));
	}
}
