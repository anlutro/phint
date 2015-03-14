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

	/** @param One[] $ones */
	public function f3(array $ones)
	{
		foreach ($ones as $one) {
			$one->two();
			$one->two;
		}
	}

	public function f4(array $ones)
	{
		/** @var One $one */
		foreach ($ones as $one) {
			$one->two();
			$one->two;
		}
	}

	/** @param DateTime[] $dts */
	public function f5($dts)
	{
		foreach ($dts as $dt) {
			$dt->modify('+1 day');
		}
	}

	public function f6()
	{
		$dts = $this->getDTs();
		foreach ($dts as $dt) {
			$dt->modify('+1 day');
		}
	}

	/**
	 * @return DateTime[]
	 */
	public function getDTs() {}
}
