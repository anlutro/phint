<?php
class StringWithVariableCase
{
	protected $p;
	protected function f() {}

	public function test($v)
	{
		"foo $v bar";
		"foo $b bar";
		"foo {$v} bar";
		"foo {$b} bar";
		"foo $this->p bar";
		"foo $this->n bar";
		"foo {$this->p} bar";
		"foo {$this->n} bar";
		"foo {$this->f()} bar";
		"foo {$this->n()} bar";
	}
}