<?php
class NestedObjectOperationsTest extends FunctionalTestCase
{
	/** @test */
	public function generated_test()
	{
		$checker = $this->makeChecker();
		$checker->addVisitor('Phint\Visitors\ChainVisitor', [$checker->makeChainFactory()]);
		$checker->check(__DIR__.'/cases/NestedObjectOperationsCase.php');
		$errors = $checker->getErrors();

		$this->assertEquals(10, count($errors));
		$this->assertEquals('26', $errors[0]->getLineNumber());
		$this->assertEquals('Call to undefined method: One::one()', $errors[0]->getMessage());
		$this->assertEquals('27', $errors[1]->getLineNumber());
		$this->assertEquals('Undefined property: One::$one', $errors[1]->getMessage());
		$this->assertEquals('30', $errors[2]->getLineNumber());
		$this->assertEquals('Call to undefined method: Two::two()', $errors[2]->getMessage());
		$this->assertEquals('31', $errors[3]->getLineNumber());
		$this->assertEquals('Undefined property: Two::$two', $errors[3]->getMessage());
		$this->assertEquals('37', $errors[4]->getLineNumber());
		$this->assertEquals('Call to undefined method: One::one()', $errors[4]->getMessage());
		$this->assertEquals('38', $errors[5]->getLineNumber());
		$this->assertEquals('Undefined property: One::$one', $errors[5]->getMessage());
		$this->assertEquals('40', $errors[6]->getLineNumber());
		$this->assertEquals('Call to undefined method: Two::two()', $errors[6]->getMessage());
		$this->assertEquals('41', $errors[7]->getLineNumber());
		$this->assertEquals('Undefined property: Two::$two', $errors[7]->getMessage());
		$this->assertEquals('57', $errors[8]->getLineNumber());
		$this->assertEquals('Cannot determine type of variable $one', $errors[8]->getMessage());
		$this->assertEquals('58', $errors[9]->getLineNumber());
		$this->assertEquals('Cannot determine type of variable $one', $errors[9]->getMessage());
	}
}
