<?php
class InstanceofCase
{
	public function f($a)
	{
		$a instanceof NonexistantClass;
		$a instanceof InstanceofCase;
	}
}
