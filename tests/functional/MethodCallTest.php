<?php
class MethodCallTest extends FunctionalTestCase
{
	/** @test */
	public function generated_test()
	{
		$errors = $this->check(__DIR__.'/cases/MethodCallCase.php');
		$this->assertEquals(2, count($errors));
		$this->assertEquals('6', $errors[0]->getLineNumber());
		$this->assertEquals('Call to undefined method: MethodCallCase::nope()', $errors[0]->getMessage());
		$this->assertEquals('14', $errors[1]->getLineNumber());
		$this->assertEquals('Call to undefined method: MethodCallCase::nope()', $errors[1]->getMessage());
	}
}
