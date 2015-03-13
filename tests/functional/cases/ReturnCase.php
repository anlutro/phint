<?php
class ReturnCase
{
	public function f1()
	{
		return 1;
	}

	public function f2($a)
	{
		return $a + 1;
	}

	public function f3()
	{
		return $a + 1;
	}

	public function f4()
	{
		return $this->f2(2);
	}
}
