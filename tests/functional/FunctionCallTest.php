<?php
class FunctionCallTest extends FunctionalTestCase
{
	/** @test */
	public function regenerate()
	{
		$errors = $this->check(__DIR__.'/cases/FunctionCallCase.php');
		$this->assertEquals(1, count($errors));
		$this->assertEquals('7', $errors[0]->getLineNumber());
		$this->assertEquals('Call to undefined function: str_to_upper', $errors[0]->getMessage());
	}
}
