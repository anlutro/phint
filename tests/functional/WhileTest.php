<?php
class WhileTest extends FunctionalTestCase
{
	/** @test */
	public function generated_test()
	{
		$errors = $this->check(__DIR__.'/cases/WhileCase.php');
		$this->assertEquals(1, count($errors));
		$this->assertEquals('22', $errors[0]->getLineNumber());
		$this->assertEquals('Undefined variable: $a', $errors[0]->getMessage());
	}
}
