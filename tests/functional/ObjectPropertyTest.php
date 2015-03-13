<?php
class ObjectPropertyTest extends FunctionalTestCase
{
	/** @test */
	public function regenerate()
	{
		$errors = $this->check(__DIR__.'/cases/ObjectPropertyCase.php');
		$this->assertEquals(3, count($errors));
		$this->assertEquals('12', $errors[0]->getLineNumber());
		$this->assertEquals('Undefined property: ObjectPropertyCase::$np', $errors[0]->getMessage());
		$this->assertEquals('13', $errors[1]->getLineNumber());
		$this->assertEquals('Undefined property: ObjectPropertyCase::$np', $errors[1]->getMessage());
		$this->assertEquals('14', $errors[2]->getLineNumber());
		$this->assertEquals('Undefined property: ObjectPropertyCase::$np', $errors[2]->getMessage());
	}
}
