<?php
class IfTest extends FunctionalTestCase
{
	/** @test */
	public function generated_test()
	{
		$errors = $this->check(__DIR__.'/cases/IfCase.php');
		$this->assertEquals(2, count($errors));
		$this->assertEquals('10', $errors[0]->getLineNumber());
		$this->assertEquals('Undefined variable: $a', $errors[0]->getMessage());
		$this->assertEquals('14', $errors[1]->getLineNumber());
		$this->assertEquals('Undefined variable: $b', $errors[1]->getMessage());
	}
}
