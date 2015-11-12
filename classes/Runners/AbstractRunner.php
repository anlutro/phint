<?php
namespace Phint\Runners;

use Phint\Checker;
use Phint\PhintException;
use Symfony\Component\Finder\Finder;

abstract class AbstractRunner
{
	/**
	 * @return Checker
	 */
	protected function makeChecker()
	{
		$checker = new Checker;
		$checker->addDefaultVisitors();
		return $checker;
	}

	protected function findAndLoadAutoloader($cwd = null)
	{
		$cwd = $cwd ?: getcwd();

		do {
			$path = $cwd.'/vendor/autoload.php';
			if (file_exists($path)) {
				require_once $path;
				return;
			}
			$cwd = dirname($cwd);
		} while ($cwd != '/');

		throw new PhintException('No autoloader found!');
	}

	protected function getPaths(array $inputs, array $excludes = [])
	{
		$dirs = [];
		$paths = [];

		if (!$inputs) {
			throw new \InvalidArgumentException("No paths given");
		}

		foreach ($inputs as $input) {
			if (is_dir($input)) {
				$dirs[] = rtrim($input, '/\\');
			} elseif (file_exists($input)) {
				$paths[] = $input;
			} else {
				throw new \InvalidArgumentException("Path does not exist: $input");
			}
		}

		if ($dirs) {
			$finder = Finder::create()
				->files()
				->in($dirs)
				->name('*.php')
				->exclude($excludes);

			foreach ($finder as $file) {
				// finder doesn't let you exclude files, only directories
				if (in_array($file->getRelativePathName(), $excludes, true)) {
					continue;
				}

				$paths[] = $file->getPathName();
			}
		}

		return array_unique($paths);
	}
}
