<?php
class ForCase
{
	public function f1()
	{
		for ($i=0; $i < 10; $i++) { 
			$i;
			$j;
			for ($j=0; $j < 10; $j++) { 
				$i;
				$j;
			}
			$j;
		}
		$i;

		for ($i=0; $i < $a; $i++) {}

		$a = 10;
		for ($i=0; $i < $a; $i++) { 
			$i;
			$a;
		}
	}
}
