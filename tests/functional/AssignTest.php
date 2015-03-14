<?php
class AssignTest extends FunctionalTestCase
{
	/** @test */
	public function generated_test()
	{
		$errors = $this->check(__DIR__.'/cases/AssignCase.php');
		$this->assertEquals(3, count($errors));
		$this->assertEquals('16', $errors[0]->getLineNumber());
		$this->assertEquals('Undefined variable: $bar', $errors[0]->getMessage());
		$this->assertEquals('21', $errors[1]->getLineNumber());
		$this->assertEquals('Call to undefined function: asdf', $errors[1]->getMessage());
		$this->assertEquals('26', $errors[2]->getLineNumber());
		$this->assertEquals('Call to undefined method: AssignCase::bar()', $errors[2]->getMessage());
	}
}
