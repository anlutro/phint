<?php
class WhileCase
{
	public function f1()
	{
		$a = 0;
		while ($a++ <= 10) {
			$a;
		}
	}

	public function f2()
	{
		while ($a = 1) {
			$a;
		}
	}

	public function f3()
	{
		do {
			$a;
		} while ($a = 1);
	}
}
