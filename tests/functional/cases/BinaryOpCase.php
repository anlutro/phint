<?php
class BinaryOpCase
{
	public function f1()
	{
		1 == 1;
		1 === 1;
		'a' == 'a';
		'a' === 'a';
		1 == '1';
		1 === '1';
		'A' == strtoupper('a');
		1 < 2;
		2 > 1;
		1 >= 1;
		1 <= 1;
		1 >= 'a';
	}

	public function f2($a)
	{
		$a == 1;
		$a === 1;
		1 == $a;
		1 === $a;
		1 == $b;
		$b == 1;
	}
}
