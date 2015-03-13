<?php
class AssignCase
{
	public function f1()
	{
		$foo = 123;
	}

	public function f2()
	{
		$foo = 'abc';
	}

	public function f3()
	{
		$foo = $bar;
	}

	public function f4()
	{
		$foo = asdf();
	}

	public function f5()
	{
		$foo = $this->bar();
	}
}
