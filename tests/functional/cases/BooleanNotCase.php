<?php
class BooleanNotCase
{
	public function f()
	{
		! true;
		! false;
		! isset($a);
		! true == false;
		! $this->f();
		! $a;
		! $this->n();
	}
}
