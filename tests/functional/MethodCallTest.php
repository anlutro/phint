<?php
class MethodCallTest extends FunctionalTestCase
{
	/** @test */
	public function regenerate()
	{
		$errors = $this->check(__DIR__.'/cases/MethodCallCase.php');
		$this->assertEquals(1, count($errors));
		$this->assertEquals('6', $errors[0]->getLineNumber());
		$this->assertEquals('Call to undefined method: MethodCallCase::nope()', $errors[0]->getMessage());
	}
}
