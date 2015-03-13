<?php
class TernaryTest extends FunctionalTestCase
{
	/** @test */
	public function regenerate()
	{
		$errors = $this->check(__DIR__.'/cases/TernaryCase.php');
		$this->assertEquals(2, count($errors));
		$this->assertEquals('9', $errors[0]->getLineNumber());
		$this->assertEquals('Undefined variable: $a', $errors[0]->getMessage());
		$this->assertEquals('10', $errors[1]->getLineNumber());
		$this->assertEquals('Undefined variable: $a', $errors[1]->getMessage());
	}
}
