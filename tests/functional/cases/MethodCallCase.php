<?php
class MethodCallCase
{
	public function f()
	{
		$this->nope();
		$this->f();
	}

	public function f_new()
	{
		$c = new MethodCallCase();
		$c->f();
		$c->nope();
	}
}
