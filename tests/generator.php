<?php
// this code generates functional tests. it is a very useful abomination

$cases = glob(dirname(__DIR__).'/tests/functional/cases/*.php');
foreach ($cases as $case) {
	$case = (str_replace('Case.php', '', basename($case)));

	$path = __DIR__.'/functional/'.$case.'Test.php';
	if (!file_exists($path)) {
		$generator = "/**/\t\t\$code = \$this->generateTests(\$errors);
\$test = file_get_contents(__FILE__);
\$test = substr_replace(\$test, \$code, strpos(\$test, '/**/'), LEN);
file_put_contents(__FILE__, \$test);/**/";
		$generator = str_replace('LEN', strlen($generator), $generator);
		$code = "<?php
class {$case}Test extends FunctionalTestCase
{
	/** @test */
	public function generated_test()
	{
		\$errors = \$this->check(__DIR__.'/cases/{$case}Case.php');
$generator	}
}
";
		file_put_contents($path, $code);
		echo "$path generated\n";
	}
}
