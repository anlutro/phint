<?php
class VariableDocblockCase
{
	public function f()
	{
		$random_str = substr(str_shuffle('asdf'), 0, 5);
		/** @var DateTime */
		$dt = $random_str();
		$dt->modify('+1 day');
	}
}
