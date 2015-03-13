<?php
class TernaryCase
{
	public function f()
	{
		true ?: false;
		true ? 'yes' : 'no';

		$a ?: null;
		$a ? 'yes' : 'no';

		$a = true;
		$a ?: null;
		$a ? 'yes' : 'no';
	}
}
