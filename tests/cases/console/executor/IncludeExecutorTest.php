<?php

namespace li3_console\tests\cases\console\executor;

use li3_console\tests\mocks\console\executor\MockIncludeExecutor;
use lithium\core\Libraries;

class IncludeExecutorTest extends \lithium\test\Unit {
    protected function countTmpFiles() {
	return count(glob(MockIncludeExecutor::tmpPath()."*.php"));
    }

    public function testClean() {
	$executor = new MockIncludeExecutor();
	$this->assertEqual(0, $this->countTmpFiles());
	$executor->invokeMethod('execute', array(array('$a = 3')));
	$this->assertEqual(1, $this->countTmpFiles());
	$executor->invokeMethod('execute', array(array('$a = 3')));
	$this->assertEqual(2, $this->countTmpFiles());
	$executor->stop();
	$this->assertEqual(0, $this->countTmpFiles());
    }

    public function testExecute() {
	$fixtures = array('simple', 'multiline');
	$path = Libraries::get('li3_console','path').'/tests/fixtures/include_executor/';
	$executor = new MockIncludeExecutor();
	foreach($fixtures as $fixture) {
	    $this->assert(is_file($path.$fixture.".php"), $path.$fixture.".php not found");
	    $this->assert(is_file($path.$fixture.".expected.ini"), $path.$fixture.".expected.ini not found");
	    $code = explode("\n", file_get_contents($path.$fixture.".php"));
	    $expected = @parse_ini_file($path.$fixture.".expected.ini", true);
	    $return = $executor->invokeMethod('execute', array($code));
	    if (array_key_exists('return_type', $expected)) {
		$this->assertTrue($return instanceof $expected['return_type']);
	    }
	    if (array_key_exists('return', $expected)) {
		$this->assertEqual($expected['return'], $return);
	    }
	    if (array_key_exists('vars', $expected) && is_array($expected['vars'])) {
		foreach($expected['vars'] as $name => $value) {
		    $this->assertEqual($value, $executor->invokeMethod('getVar', array($name)));
		}
	    }
	    if (array_key_exists('vars_check_type', $expected) && is_array($expected['vars_check_type'])) {
		foreach($expected['vars_check_type'] as $name => $value) {
		    $this->assertTrue($executor->invokeMethod('getVar', array($name)) instanceof $value);
		}
	    }
	}
	$executor->stop();
    }
}

?>
