<?php
namespace Phint;

class ErrorBag
{
	protected $errors = [];

	public function add(Error $error)
	{
		$this->errors[] = $error;
	}

	public function getAll()
	{
		return $this->errors;
	}

	public function clear()
	{
		$this->errors = [];
	}
}
