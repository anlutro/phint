<?php
class TypeMismatchTest extends FunctionalTestCase
{
	/** @test */
	public function generated_test()
	{
		$errors = $this->check(__DIR__.'/cases/TypeMismatchCase.php');
		$this->assertEquals(1, count($errors));
		$this->assertEquals('8', $errors[0]->getLineNumber());
		$this->assertEquals('@param docblock and type-hint mismatch for argument $v', $errors[0]->getMessage());
	}
}
