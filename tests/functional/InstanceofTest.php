<?php
class InstanceofTest extends FunctionalTestCase
{
	/** @test */
	public function generated_test()
	{
		$errors = $this->check(__DIR__.'/cases/InstanceofCase.php');
		$this->assertEquals(1, count($errors));
		$this->assertEquals('6', $errors[0]->getLineNumber());
		$this->assertEquals('Testing instanceof against non-existant class: NonexistantClass', $errors[0]->getMessage());
	}
}
