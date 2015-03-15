<?php
abstract class FunctionalTestCase extends PHPUnit_Framework_TestCase
{
	protected function generateTests(array $errors)
	{
		$str = '';
		$str .= "\t\t".'$this->assertEquals('.count($errors).', count($errors));'."\n";
		foreach ($errors as $key => $error) {
			$str .= "\t\t".'$this->assertEquals(\''.$error->getLineNumber().'\', $errors['.$key.']->getLineNumber());'."\n";
			$str .= "\t\t".'$this->assertEquals(\''.$error->getMessage().'\', $errors['.$key.']->getMessage());'."\n";
		}
		return $str;
	}

	protected function check($path)
	{
		$checker = $this->makeChecker();
		$checker->check($path);
		return $checker->getErrors();
	}

	protected function makeChecker()
	{
		$checker = new Phint\Checker();
		$checker->addDefaultVisitors();
		return $checker;
	}
}
