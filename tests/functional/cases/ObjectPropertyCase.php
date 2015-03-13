<?php
class ObjectPropertyCase
{
	protected $p;

	public function f()
	{
		$this->p;
		$a = $this->p;
		$this->p = true;

		$this->np;
		$a = $this->np;
		$this->np = true;
	}
}
