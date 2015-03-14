<?php
class BooleanNotTest extends FunctionalTestCase
{
	/** @test */
	public function generated_test()
	{
		$errors = $this->check(__DIR__.'/cases/BooleanNotCase.php');
		$this->assertEquals(2, count($errors));
		$this->assertEquals('11', $errors[0]->getLineNumber());
		$this->assertEquals('Undefined variable: $a', $errors[0]->getMessage());
		$this->assertEquals('12', $errors[1]->getLineNumber());
		$this->assertEquals('Call to undefined method: BooleanNotCase::n()', $errors[1]->getMessage());
	}
}
