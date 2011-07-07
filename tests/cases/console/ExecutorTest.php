<?php

namespace li3_console\tests\cases\console;

use li3_console\tests\mocks\console\MockExecutor;
use lithium\core\Libraries;

class ExecutorTest extends \lithium\test\Unit {
    public $var_cases = array(
	array (
	    'code' => array ('$a = 3 + 4'),
	    'expected' => array ('a'),
	),
	array (
	    'code' => array (
		'$a = 3 + 4;',
		'$b = "foo"',
	    ),
	    'expected' => array ('a', 'b'),
	),
	array (
	    'code' => array (
		'$a = 3 + 4;',
		'$b = "foo";',
		'$c = function() {',
		'  $d = 4;',
		'  $e = "bar";',
		'}',
	    ),
	    'expected' => array ('a', 'b', 'c'),
	),
    );

    public function testExtractVarNames() {
	$executor = new MockExecutor();
	foreach($this->var_cases as $case) {
	    $this->assertEqual($case['expected'], $executor->invokeMethod('extractVarNames', array($case['code'])));
	}
    }

    public function testExtractAndStoreVars() {
	foreach($this->var_cases as $case) {
	    $executor = new MockExecutor();
	    $executor->invokeMethod('extractAndStoreVars', array($case['code']));
	    $this->assertEqual($case['expected'], $executor->invokeMethod('varNames'));
	    foreach($case['expected'] as $name) {
		$this->assertEqual(null, $executor->invokeMethod('getVar', array($name)));
	    }
	}
    }

    public function testCodeStringGeneration() {
	$fixtures = array('simple', 'simple_with_uses', 'invalid_syntax');
	$path = Libraries::get('li3_console','path').'/tests/fixtures/executor/';
	$executor = new MockExecutor(array('add_classes' => array()));
	foreach($fixtures as $fixture) {
	    $this->assert(is_file($path.$fixture.".php"), $path.$fixture.".php not found");
	    $this->assert(is_file($path.$fixture.".expected.php"), $path.$fixture.".expected.php not found");
	    if (is_file($path.$fixture.".ini")) {
		$options = @parse_ini_file($path.$fixture.".ini");
	    } else {
		$options = array();
	    }
	    $code = explode("\n", file_get_contents($path.$fixture.".php"));
	    $codeStr = $executor->invokeMethod('codeString', array($code, $options));
	    $expected = file_get_contents($path.$fixture.".expected.php");
	    $this->assertEqual($expected, $codeStr, "fixture {$fixture} failed, generated string is:\n{$codeStr}\n");
	}
    }

    public function testRunCallsExecute() {
	$executor = new MockExecutor();
	$this->assertEqual(0, $executor->executed);
	$executor->run(array('$a = 3'));
	$this->assertEqual(1, $executor->executed);
    }

    public function testSetsResourcePath() {
	$executor = new MockExecutor(array('resources_path' => 'foo/bar/'));
	$this->assertEqual('foo/bar/name', $executor->invokeMethod('resource', array('name')));
    }


/*    public function testFoo() {
	$e = new MockExecutor();
	var_dump($e->invokeMethod('codeString', array (array('$a=3'))));
	var_dump($e->invokeMethod('codeString', array (array('$a=3; $b=4;', '$d="e"; $f="g"'))));
	var_dump($e->invokeMethod('codeString', array (array('$foo = function() {', '}'))));
	exit;
    }*/
}

?>
