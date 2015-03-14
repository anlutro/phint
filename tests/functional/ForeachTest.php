<?php
class ForeachTest extends FunctionalTestCase
{
	/** @test */
	public function generated_test()
	{
		$errors = $this->check(__DIR__.'/cases/ForeachCase.php');
		$this->assertEquals(2, count($errors));
		$this->assertEquals('6', $errors[0]->getLineNumber());
		$this->assertEquals('Undefined variable: $key', $errors[0]->getMessage());
		$this->assertEquals('7', $errors[1]->getLineNumber());
		$this->assertEquals('Undefined variable: $value', $errors[1]->getMessage());
	}
}
