<?php
class ForTest extends FunctionalTestCase
{
	/** @test */
	public function regenerate()
	{
		$errors = $this->check(__DIR__.'/cases/ForCase.php');
		$this->assertEquals(2, count($errors));
		$this->assertEquals('8', $errors[0]->getLineNumber());
		$this->assertEquals('Undefined variable: $j', $errors[0]->getMessage());
		$this->assertEquals('17', $errors[1]->getLineNumber());
		$this->assertEquals('Undefined variable: $a', $errors[1]->getMessage());
	}
}
