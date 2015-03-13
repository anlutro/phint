<?php
class StringWithVariableTest extends FunctionalTestCase
{
	/** @test */
	public function regenerate()
	{
		$errors = $this->check(__DIR__.'/cases/StringWithVariableCase.php');
		$this->assertEquals(5, count($errors));
		$this->assertEquals('10', $errors[0]->getLineNumber());
		$this->assertEquals('Undefined variable: $b', $errors[0]->getMessage());
		$this->assertEquals('12', $errors[1]->getLineNumber());
		$this->assertEquals('Undefined variable: $b', $errors[1]->getMessage());
		$this->assertEquals('14', $errors[2]->getLineNumber());
		$this->assertEquals('Undefined property: StringWithVariableCase::$n', $errors[2]->getMessage());
		$this->assertEquals('16', $errors[3]->getLineNumber());
		$this->assertEquals('Undefined property: StringWithVariableCase::$n', $errors[3]->getMessage());
		$this->assertEquals('18', $errors[4]->getLineNumber());
		$this->assertEquals('Call to undefined method: StringWithVariableCase::n()', $errors[4]->getMessage());
	}
}
