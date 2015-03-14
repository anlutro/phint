<?php
class One
{
	/** @var Two */
	public $two;

	/** @return Two */
	public function two() {}
}

class Two
{
	/** @var One */
	public $one;

	/** @return One */
	public function one() {}
}

class NestedObjectOperationsCase
{
	public function f1()
	{
		$one = new One;
		$one->two->one->two()->one()->two()->one->two->one;
		$one->two->one->one()->two()->one();
		$one->two->one->one->two()->one();
		$two = new Two;
		$two->one->two->one()->two()->one()->two->one->two;
		$two->one->two->two()->one()->two();
		$two->one->two->two->one()->two();
	}

	public function f2(One $one, Two $two)
	{
		$one->two->one->two()->one()->two()->one->two->one;
		$one->two->one->one()->two()->one();
		$one->two->one->one->two()->one();
		$two->one->two->one()->two()->one()->two->one->two;
		$two->one->two->two()->one()->two();
		$two->one->two->two->one()->two();
	}
}