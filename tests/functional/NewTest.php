<?php
class NewTest extends FunctionalTestCase
{
	/** @test */
	public function regenerate()
	{
		$errors = $this->check(__DIR__.'/cases/NewCase.php');
		$this->assertEquals(1, count($errors));
		$this->assertEquals('8', $errors[0]->getLineNumber());
		$this->assertEquals('Tried instantiating non-existant class: NonExistant', $errors[0]->getMessage());
	}
}
