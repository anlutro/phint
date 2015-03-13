For very small changes or bugfixes, feel free to submit a pull request right away.

For anything more involved, please open an issue to discuss the change before the code is written.

### Making pull requests

Fork the package on Github and clone the forked repository. Add the original repository as upstream. Install the dependencies and check that tests pass. (Remember to replace $FORKED_REPOSITORY in the example below!)

```
git clone $FORKED_REPOSITORY && cd !$
git remote add upstream https://github.com/anlutro/phint
composer install
phpunit --verbose
```

Creating a new branch for each pull request is highly recommended for your own sake.
