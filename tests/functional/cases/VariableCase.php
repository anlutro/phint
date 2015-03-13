<?php
class VariableCase
{
	public function f1()
	{
		$a = 'foo';
		$b = $a;
	}

	public function f2()
	{
		$b = $a;
	}
}
