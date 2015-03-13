<?php
class ForeachCase
{
	public function f1($variable)
	{
		$key;
		$value;
		foreach ($variable as $key => $value) {
			$key;
			$value;
		}
		$key;
		$value;
	}
}
