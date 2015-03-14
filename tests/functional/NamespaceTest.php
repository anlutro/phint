<?php
class NamespaceTest extends FunctionalTestCase
{
	/** @test */
	public function generated_test()
	{
		$errors = $this->check(__DIR__.'/cases/NamespaceCase.php');
		$this->assertEquals(0, count($errors));
	}
}
