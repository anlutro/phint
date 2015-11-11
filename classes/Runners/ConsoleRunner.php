<?php
namespace Phint\Runners;

class ConsoleRunner extends AbstractRunner
{
	protected $cwd;
	protected $hasErrors = false;
	protected $exitEarly = false;
	protected $excludes = [];

	/**
	 * Run the check.
	 *
	 * @param  string[]  $input
	 *
	 * @return int
	 */
	public function run(array $input)
	{
		$this->extractFlags($input);

		$this->findAndLoadAutoloader($this->cwd);

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

	protected function extractFlags(array &$inputs)
	{
		$matches = [];
		foreach ($inputs as $key => $input) {
			preg_match('/(--[a-z-_]+|-[a-z])(=([a-zA-Z-._\/\\\\]+))?/', $input, $match);
			if ($match) {
				$matches[] = $match;
				unset($inputs[$key]);
			}
		}

		foreach ($matches as $match) {
			switch ($match[1]) {
				case '--exclude':
					$this->excludes[] = $match[3];
					break;
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
				case '-p':
				case '--path':
					$this->cwd = $match[3];
				default:
					break;
			}
		}
	}

	/**
	 * @param  string[]  $inputs
	 * @param  string[]  $excludes
	 *
	 * @return string[]
	 */
	protected function getPaths(array $inputs, array $excludes = [])
	{
		$excludes = array_merge($this->excludes, $excludes);

		return parent::getPaths($inputs, $excludes);
	}

	protected function check($path)
	{
		$checker = $this->makeChecker();
		$checker->check(realpath($path));

		if ($errors = $checker->getErrors()) {
			if ($this->hasErrors) {
				echo "\n";
			}
			echo "\e[33mErrors in $path:\e[0m\n";
			$longestLine = 0;
			foreach ($errors as $error) {
				$longestLine = strlen($error->getLineNumber());
			}
			foreach ($errors as $error) {
				$line = str_pad('L'.$error->getLineNumber(), $longestLine + 3);
				echo "\e[31m".$line."\e[0m".$error->getMessage()."\n";
			}
			$this->hasErrors = true;
		}

		return ! $this->hasErrors;
	}
}
