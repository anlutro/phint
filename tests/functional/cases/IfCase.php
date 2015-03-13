<?php
class IfCase
{
	public function f()
	{
		if (true) {}
		if (true) {} else {}
		if (true) {} elseif (! false) {} else {}

		if ($a) {}
		$a = true;
		if ($a) {}

		if ($a) {} elseif ($b) {}
		$b = true;
		if ($a) {} elseif ($b) {}
	}
}
