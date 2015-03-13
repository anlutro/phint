<?php
class MethodCallCase
{
	public function f()
	{
		$this->nope();
		$this->f();
	}
}
