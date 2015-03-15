<?php
namespace Phint\Runners;

use Phint\Checker;
use Symfony\Component\Finder\Finder;

class ConsoleRunner
{
	protected $finder;
	protected $hasErrors = false;
	protected $exitEarly = false;

	public function __construct()
	{
		$this->finder = new Finder;
	}

	/**
	 * Run the check.
	 *
	 * @param  string[]  $input
	 *
	 * @return int
	 */
	public function run(array $input)
	{
		$flags = $this->extractFlags($input);
		$paths = $this->getPaths($input);

		if (!defined('PHINT_DEBUG')) {
			define('PHINT_DEBUG', false);
		}
		if (!defined('PHINT_STRICT')) {
			define('PHINT_STRICT', false);
		}

		foreach ($paths as $path) {
			if (PHINT_DEBUG) {
				echo "Checking $path\n";
			}
			$result = $this->check($path);
			if (!$result && $this->exitEarly) {
				break;
			}
		}

		if ($this->hasErrors) {
			return 1;
		} else {
			echo "No errors found!\n";
			return 0;
		}
	}

	private function extractFlags(array &$inputs)
	{
		$flags = [];

		foreach ($inputs as $key => $input) {
			preg_match('/(\-\-[a-z\-]+|\-[a-z])/', $input, $matches);
			if ($matches) {
				$flags[] = $matches[1];
				unset($inputs[$key]);
			}
		}

		foreach ($flags as $flag) {
			switch ($flag) {
				case '-e':
				case '--exit-early':
					$this->exitEarly = true;
					break;
				case '-s':
				case '--strict':
					define('PHINT_STRICT', true);
					break;
				case '-d':
				case '--debug':
					define('PHINT_DEBUG', true);
					break;
				default:
					break;
			}
		}
	}

	/**
	 * @param  string[]  $inputs
	 *
	 * @return string[]
	 */
	private function getPaths(array $inputs)
	{
		$dirs = [];
		$paths = [];
		$excludes = [];

		foreach ($inputs as $key => $input) {
			if (strpos($input, '--exclude=') === 0) {
				$excludes[] = substr($input, 10);
				unset($inputs[$key]);
			}
		}

		if (!$inputs) {
			throw new \InvalidArgumentException("No paths given");
		}

		foreach ($inputs as $input) {
			if (is_dir($input)) {
				$dirs[] = $input;
			} elseif (file_exists($input)) {
				$paths[] = $input;
			} else {
				throw new \InvalidArgumentException("Path does not exist: $input");
			}
		}

		if ($dirs) {
			$finder = Finder::create()
				->files()
				->name('*.php')
				->exclude($excludes)
				->in($dirs);

			foreach ($finder as $file) {
				$paths[] = $file->getPathName();
			}
		}

		foreach ($paths as $key => $value) {
			foreach ($excludes as $exclude) {
				if (strpos($value, $exclude) === 0) {
					unset($paths[$key]);
					continue 2;
				}
			}
		}

		return array_unique($paths);
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

		return ! $this->hasErrors;
	}

	/**
	 * @return Checker
	 */
	private function makeChecker()
	{
		$checker = new Checker;
		$checker->addDefaultVisitors();
		return $checker;
	}
}
