<?php
class BinaryOpTest extends FunctionalTestCase
{
	/** @test */
	public function regenerate()
	{
		$errors = $this->check(__DIR__.'/cases/BinaryOpCase.php');
		$this->assertEquals(2, count($errors));
		$this->assertEquals('26', $errors[0]->getLineNumber());
		$this->assertEquals('Undefined variable: $b', $errors[0]->getMessage());
		$this->assertEquals('27', $errors[1]->getLineNumber());
		$this->assertEquals('Undefined variable: $b', $errors[1]->getMessage());
	}
}
