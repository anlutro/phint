<?php
namespace Phint\Runners;

use Phint\Checker;
use Symfony\Component\Finder\Finder;

class ConsoleRunner
{
	protected $finder;
	protected $hasErrors = false;

	public function __construct()
	{
		$this->finder = new Finder;
	}

	public function run(array $input)
	{
		$paths = $this->getPaths($input);

		foreach ($paths as $path) {
			$this->check($path);
		}

		if ($this->hasErrors) {
			return 1;
		} else {
			echo "No errors found!\n";
			return 0;
		}
	}

	private function getPaths(array $inputs)
	{
		$paths = [];

		foreach ($inputs as $input) {
			if (is_dir($input)) {
				$files = $this->findFiles($input);
				$paths = array_merge($paths, $files);
			} else {
				$paths[] = $input;
			}
		}

		return array_unique($paths);
	}

	private function findFiles($dir)
	{
		$finder = new Finder;
		$files = $finder
			->files()
			->name('*.php')
			->in($dir);
		$files = iterator_to_array($files);
		return array_keys($files);
	}

	private function check($path)
	{
		$checker = $this->makeChecker();
		$checker->check(realpath($path));

		if ($errors = $checker->getErrors()) {
			if ($this->hasErrors) {
				echo "\n";
			}
			echo "Errors in $path:\n";
			$longestLine = 0;
			foreach ($errors as $error) {
				$longestLine = strlen($error->getLineNumber());
			}
			foreach ($errors as $error) {
				$line = str_pad('L'.$error->getLineNumber(), $longestLine + 3);
				echo $line.$error->getMessage()."\n";
			}
			$this->hasErrors = true;
		}
	}

	private function makeChecker()
	{
		$checker = new Checker;
		$checker->addDefaultVisitors();
		return $checker;
	}
}
