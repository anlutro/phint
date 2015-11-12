<?php
namespace Phint\Runners;

class ConsoleRunner extends AbstractRunner
{
	protected $cwd;
	protected $hasErrors = false;
	protected $numErrors = 0;
	protected $numFilesWithErrors = 0;
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
			echo "\n";
		}

		echo "Successfully scanned ".count($paths)." files\n";
		if ($this->hasErrors) {
			echo "\e[1;41m {$this->numErrors} errors found in {$this->numFilesWithErrors} files! \e[0m\n";
		} else {
			echo "\e[1;42m No errors found! \e[0m\n";
		}

		return $this->hasErrors ? 1 : 0;

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
		$printedErrors = [];

		if ($errors = $checker->getErrors()) {
			$this->numFilesWithErrors++;
			if ($this->hasErrors) {
				echo "\n";
			}
			echo "\e[33mErrors in $path:\e[0m\n";
			$longestLine = 0;
			foreach ($errors as $error) {
				$strlen = strlen($error->getLineNumber());
				if ($strlen > $longestLine) {
					$longestLine = $strlen;
				}
			}
			foreach ($errors as $error) {
				$line = str_pad('L'.$error->getLineNumber(), $longestLine + 3);
				$out = "\e[31m".$line."\e[0m".$error->getMessage()."\n";
				if (in_array($out, $printedErrors, true)) {
					continue;
				}
				$this->numErrors++;
				echo $out;
				$printedErrors[] = $out;
			}
			$this->hasErrors = true;
		}

		return ! $this->hasErrors;
	}
}
