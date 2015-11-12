<?php
class VariableDocblockTest extends FunctionalTestCase
{
	/** @test */
	public function generated_test()
	{
		$errors = $this->check(__DIR__.'/cases/VariableDocblockCase.php');
		$this->assertEquals(0, count($errors));
	}
}
