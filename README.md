# Phint

[![Build Status](https://travis-ci.org/anlutro/phint.png?branch=master)](https://travis-ci.org/anlutro/phint)
[![Latest Stable Version](https://poser.pugx.org/anlutro/phint/v/stable.svg)](https://github.com/anlutro/phint/releases)
![Latest Unstable Version](https://poser.pugx.org/anlutro/phint/v/unstable.svg)
![License](https://poser.pugx.org/anlutro/phint/license.svg)

Phint is a static code analysis tool for PHP. Very much a work in progress. Currently checks for:

- Undefined variables
- Properties/methods being called on `$this` exist
- Classes being instantiated and checked against with `instanceof` actually exist
- Functions being call actually exist

Features on the roadmap:

- Validate property/method calls on non-`$this` objects
- Verify that arguments with the right types are passed to function/method calls

More features to come.

## Installation and usage

On a per-project basis:

	composer require anlutro/phint
	./vendor/bin/phint /path/to/src

Globally:

	wget http://lutro.me/phint.phar
	chmod +x phint.phar
	sudo mv phint.phar /usr/local/bin
	phint /path/to/src

Example output:

	$ phint src/MyClass.php 
	Errors in src/MyClass.php:
	L6   Undefined variable: $foo
	L12  Tried instantiating non-existant class: Nonexistant

## Contributing

See the [CONTRIBUTING.md](CONTRIBUTING.md) file for information on contributing.

## License

The contents of this repository is released under the [GPL v3 license](http://opensource.org/licenses/GPL-3.0). See the [LICENSE](LICENSE) file included for more information.
