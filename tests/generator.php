<?php
// this code generates functional tests. it is a very useful abomination

$visitors = glob(dirname(__DIR__).'/classes/Visitors/*.php');
foreach ($visitors as $visitor) {
	$visitor = (str_replace('Visitor.php', '', basename($visitor)));

	$path = __DIR__.'/functional/cases/'.$visitor.'Case.php';
	if (!file_exists($path)) {
		continue;
	}

	$path = __DIR__.'/functional/'.$visitor.'Test.php';
	if (!file_exists($path)) {
		$generator = "/**/\t\t\$code = \$this->generateTests(\$errors);
\$test = file_get_contents(__FILE__);
\$test = substr_replace(\$test, \$code, strpos(\$test, '/**/'), LEN);
file_put_contents(__FILE__, \$test);/**/";
		$generator = str_replace('LEN', strlen($generator), $generator);
		$code = "<?php
class {$visitor}Test extends FunctionalTestCase
{
	/** @test */
	public function regenerate()
	{
		\$errors = \$this->check(__DIR__.'/cases/{$visitor}Case.php');
$generator	}
}
";
		file_put_contents($path, $code);
		echo "$path generated\n";
	}
}
