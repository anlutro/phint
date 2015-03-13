<?php
class ReturnTest extends FunctionalTestCase
{
	/** @test */
	public function regenerate()
	{
		$errors = $this->check(__DIR__.'/cases/ReturnCase.php');
		$this->assertEquals(1, count($errors));
		$this->assertEquals('16', $errors[0]->getLineNumber());
		$this->assertEquals('Undefined variable: $a', $errors[0]->getMessage());
	}
}
